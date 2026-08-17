<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article116Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-dashboard-on-off-relay-flask';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'python', 'flask', 'html', 'javascript', 'sqlite', 'esp32', 'mqtt'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Nyalakan dan matikan sakelar dari halaman browser',
            'title_en' => 'Turn the switch on and off from the browser page',
            'excerpt' => 'FS-46 / #116: tombol ON/OFF di http://127.0.0.1:5000, POST /command, status Perintah terkirim. Bukan MySQL. Belum Telegram.',
            'excerpt_en' => 'FS-46 / #116: ON/OFF buttons at http://127.0.0.1:5000, POST /command, status Perintah terkirim. Not MySQL. No Telegram yet.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Tombol ON/OFF Dashboard — FS-46',
            'seo_title_en' => 'Show ON/OFF Buttons — FS-46',
            'seo_description' => 'Lab pemula: tombol ON/OFF di Flask, POST /command, GET /status, MQTTX. Jangan MySQL, jangan file://, jangan AC 220V.',
            'seo_description_en' => 'A first lab: ON/OFF buttons on Flask, POST /command, GET /status, MQTTX. Not MySQL, not file://, not AC mains.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'flask', 'html', 'javascript', 'sqlite', 'esp32', 'mqtt'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs46-cover-control.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs46-cover-control.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs46-cover-control.webp',
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
            '    .angka { font-size: 3rem; font-weight: 700; }',
            '    .bingkai { max-width: 100%; }',
            '    button { font-size: 1.1rem; padding: 0.6rem 1.2rem; margin-right: 0.5rem; }',
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
            '  <p id="status-grafik">Menunggu data…</p>',
            '  <p>Sakelar</p>',
            '  <p id="sakelar">Sakelar: belum diketahui</p>',
            '  <p>',
            '    <button id="tombol-on" type="button">ON</button>',
            '    <button id="tombol-off" type="button">OFF</button>',
            '  </p>',
            '  <p id="status">Menunggu perintah…</p>',
            '  <script>',
            '    const DEVICE = "esp32-meja-01";',
            '    let sedang = false;',
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
            '          document.getElementById("status-grafik").textContent =',
            '            baris.length ? "Grafik tampil." : "Belum ada titik dalam 1 jam.";',
            '        })',
            '        .catch(() => {',
            '          document.getElementById("status-grafik").textContent = "Pintu Flask belum terbuka.";',
            '        });',
            '    }',
            '',
            '    function tulisSakelar(relay) {',
            '      const el = document.getElementById("sakelar");',
            '      if (!relay) {',
            '        el.textContent = "Sakelar: belum diketahui";',
            '        return;',
            '      }',
            '      el.textContent = relay === "on" ? "Sakelar: ON" : "Sakelar: OFF";',
            '    }',
            '',
            '    function kunciTombol(kunci) {',
            '      sedang = kunci;',
            '      document.getElementById("tombol-on").disabled = kunci;',
            '      document.getElementById("tombol-off").disabled = kunci;',
            '    }',
            '',
            '    function muatStatus() {',
            '      fetch("/status")',
            '        .then((r) => r.json())',
            '        .then((data) => {',
            '          tulisSakelar(data.relay);',
            '        })',
            '        .catch(() => {',
            '          document.getElementById("status").textContent = "Pintu Flask belum terbuka.";',
            '        });',
            '    }',
            '',
            '    function kirim(relay) {',
            '      if (sedang) return;',
            '      kunciTombol(true);',
            '      document.getElementById("status").textContent = "Mengirim…";',
            '      fetch("/command", {',
            '        method: "POST",',
            '        headers: { "Content-Type": "application/json" },',
            '        body: JSON.stringify({ device_id: DEVICE, relay: relay }),',
            '      })',
            '        .then((r) => r.json().then((data) => ({ okHttp: r.ok, data })))',
            '        .then(({ okHttp, data }) => {',
            '          if (okHttp && data.ok) {',
            '            document.getElementById("status").textContent = "Perintah terkirim.";',
            '            tulisSakelar(relay);',
            '          } else {',
            '            document.getElementById("status").textContent = data.pesan || "Perintah gagal.";',
            '          }',
            '        })',
            '        .catch(() => {',
            '          document.getElementById("status").textContent = "Pintu Flask belum terbuka.";',
            '        })',
            '        .finally(() => {',
            '          kunciTombol(false);',
            '        });',
            '    }',
            '',
            '    document.getElementById("tombol-on").addEventListener("click", () => kirim("on"));',
            '    document.getElementById("tombol-off").addEventListener("click", () => kirim("off"));',
            '    muat();',
            '    muatStatus();',
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
            'def pastikan_tabel_perintah():',
            '    with sqlite3.connect(DB_PATH) as db:',
            '        db.execute(',
            '            """',
            '            CREATE TABLE IF NOT EXISTS commands (',
            '                id INTEGER PRIMARY KEY AUTOINCREMENT,',
            '                sent_at TEXT NOT NULL,',
            '                device_id TEXT,',
            '                relay TEXT,',
            '                topic TEXT,',
            '                payload TEXT',
            '            )',
            '            """',
            '        )',
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
            '@app.get("/status")',
            'def status():',
            '    if not DB_PATH.exists():',
            '        return jsonify({"ok": False, "relay": None, "pesan": "Berkas stasiun.db belum ada. Ulangi FS-40."}), 503',
            '    device_id = (request.args.get("device_id") or "esp32-meja-01").strip() or "esp32-meja-01"',
            '    pastikan_tabel_perintah()',
            '    with sqlite3.connect(DB_PATH) as db:',
            '        db.row_factory = sqlite3.Row',
            '        row = db.execute(',
            '            "SELECT sent_at, device_id, relay FROM commands WHERE device_id = ? ORDER BY id DESC LIMIT 1",',
            '            (device_id,),',
            '        ).fetchone()',
            '    if row is None:',
            '        return jsonify({"ok": True, "device_id": device_id, "relay": None, "pesan": "Belum ada perintah."})',
            '    return jsonify({"ok": True, "device_id": row["device_id"], "relay": row["relay"], "sent_at": row["sent_at"]})',
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
            '        client_id="fsiot-fs46-pintu",',
            '    )',
            '    try:',
            '        client.connect(BROKER, MQTT_PORT, keepalive=60)',
            '    except OSError as error:',
            '        return jsonify({"ok": False, "pesan": "Broker belum terbuka di 127.0.0.1:1883", "error": str(error)}), 503',
            '    client.publish(topic, payload)',
            '    client.disconnect()',
            '    if DB_PATH.exists():',
            '        pastikan_tabel_perintah()',
            '        sent_at = datetime.now(timezone.utc).astimezone().isoformat(timespec="seconds")',
            '        with sqlite3.connect(DB_PATH) as db:',
            '            db.execute(',
            '                "INSERT INTO commands (sent_at, device_id, relay, topic, payload) VALUES (?, ?, ?, ?, ?)",',
            '                (sent_at, device_id, relay, topic, payload),',
            '            )',
            '    return jsonify({"ok": True, "topic": topic, "payload": payload, "relay": relay})',
            '',
            '',
            'if __name__ == "__main__":',
            '    print("Pintu stasiun terbuka di http://127.0.0.1:5000")',
            '    print("Buka http://127.0.0.1:5000")',
            '    print("POST http://127.0.0.1:5000/command")',
            '    print("GET  http://127.0.0.1:5000/status")',
            '    app.run(host=HOST, port=PORT, debug=False)',
        ]);
    }

    private function body(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $dashboard = htmlspecialchars($this->dashboard(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs46-tools-order.png', 'Urutan lima langkah: browser, MQTTX Subscribe command, Notepad, PowerShell Flask, browser klik ON', '<strong>Urutan meja kerja (lima langkah):</strong> browser → MQTTX Connect <code>127.0.0.1:1883</code> lalu Subscribe topic command → Notepad menulis <code>dashboard.html</code> dan <code>pintu_stasiun.py</code> → PowerShell Flask → browser klik ON sampai <code>Perintah terkirim.</code> Diagram buatan Koding Indonesia (FS-46).');
        $why = $this->figure('fs46-why-buttons.png', 'Tiga kotak: grafik FS-45, tombol ON OFF, status Sakelar ON Perintah terkirim', '<strong>Kemarin garis tren. Hari ini tombol sakelar.</strong> Baca dari kiri ke kanan: grafik → POST <code>/command</code> → status <code>Sakelar: ON</code>. Diagram buatan Koding Indonesia (FS-46).');
        $post = $this->figure('fs46-post-flow.png', 'Alur kiri ke kanan: tombol ON, POST command, Mosquitto 1883, MQTTX relay on', '<strong>Gambar utama — klik tombol, Flask meneruskan perintah.</strong> Baca dari kiri ke kanan: ON → POST <code>/command</code> → broker → MQTTX. Diagram buatan Koding Indonesia (FS-46).');
        $double = $this->figure('fs46-double-submit.png', 'Tiga kotak: klik ON sekali kunci tombol, fetch POST Mengirim, selesai buka tombol', '<strong>Jaga tombol: satu klik, satu kirim.</strong> Baca dari kiri ke kanan: klik → kunci → tunggu jawaban → buka lagi. Diagram buatan Koding Indonesia (FS-46).');
        $status = $this->figure('fs46-status.png', 'Alur kiri ke kanan: buka halaman, GET status, JSON relay, tulis Sakelar ON', '<strong>Baca status supaya UI tidak ketinggalan.</strong> Baca dari kiri ke kanan: buka halaman → GET <code>/status</code> → tulis <code>Sakelar: ON</code>. Diagram buatan Koding Indonesia (FS-46).');
        $flaskServe = $this->figure('fs46-flask-serve.png', 'Alur kiri ke kanan: GET slash, GET status, POST command, MQTT, teks Perintah terkirim', '<strong>Flask menyajikan halaman, status, dan perintah.</strong> Baca dari kiri ke kanan: GET <code>/</code> → GET <code>/status</code> → POST <code>/command</code> → MQTT. Diagram buatan Koding Indonesia (FS-46).');
        $browser = $this->figure('fs46-browser-panel.png', 'Ilustrasi jendela browser menampilkan judul Stasiun meja, Sakelar ON, tombol ON OFF, status Perintah terkirim', '<strong>Browser sudah menampilkan tombol sakelar.</strong> Alamat yang dikunci adalah <code>http://127.0.0.1:5000</code> dan teks <code>Perintah terkirim.</code> Grafik kemarin boleh tetap. Ilustrasi buatan Koding Indonesia (FS-46), meniru jendela browser. Tampilan resmi tidak dipakai utuh.');
        $mqttx = $this->figure('fs46-mqttx.png', 'Ilustrasi MQTTX Connected ke 127.0.0.1:1883 menampilkan JSON relay on di topic command', '<strong>MQTTX sudah menampilkan perintah relay.</strong> Connect dulu, baru Subscribe. Jangan tekan Publish. Ilustrasi buatan Koding Indonesia (FS-46), meniru <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0). Tangkapan layar resmi tidak dipakai utuh.');
        $troubleshooting = $this->figure('fs46-troubleshooting.png', 'Empat pemeriksaan: MQTTX, Flask, alamat file versus http, broker 1883', '<strong>Skema bantu.</strong> MQTTX Connected. Flask ke <code>127.0.0.1:5000</code>. Jangan <code>file://</code>. Diagram buatan Koding Indonesia (FS-46).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan ketik perintah Python dulu.'],
            ['title' => 'Buka MQTTX', 'text' => 'Connect ke <code>127.0.0.1:1883</code>. Tekan Subscribe pada topic <code>kodingindonesia/fsiot/esp32-meja-01/command</code>. Jangan Publish dulu.'],
            ['title' => 'Buka Notepad, tulis berkas', 'text' => 'Perbarui <code>dashboard.html</code> dan <code>pintu_stasiun.py</code>. All files, bukan <code>.txt</code>.'],
            ['title' => 'Buka PowerShell, jalankan Flask', 'text' => 'Start → ketik PowerShell. Tidak perlu <em>Run as administrator</em>. Jalankan Flask. Jendela Flask tetap terbuka.'],
            ['title' => 'Buka browser, klik ON', 'text' => 'Tab baru: <code>http://127.0.0.1:5000</code> — bukan <code>file://</code>. Klik ON. Status <code>Perintah terkirim.</code>'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = browser menampilkan <code>Perintah terkirim.</code> plus <code>Sakelar: ON</code>, dan MQTTX menampilkan JSON <code>relay":"on"</code>. ESP32 boleh menyala, tetapi tidak wajib.');

        return <<<'HTML'
<h2>Pendahuluan — tombol, bukan hanya grafik</h2>
<p><strong>FS-46 / #116 (ini)</strong> adalah lab panel kontrol. Kemarin halaman sudah menampilkan garis tren suhu. Hari ini tugasnya lain: <strong>nyalakan dan matikan sakelar dari browser</strong>, supaya perintah tidak lagi diketik di script.</p>
<p><strong>Intinya:</strong> pasang tombol ON/OFF di <code>dashboard.html</code>, kirim POST <code>/command</code>, baca GET <code>/status</code>, sampai teks <code>Perintah terkirim.</code> dan <code>Sakelar: ON</code> tampil di <code>http://127.0.0.1:5000</code>.</p>
<p><strong>Analogi:</strong> grafik adalah kaca spion. Tombol adalah stang: kamu menggerakkan sakelar, lalu halaman bilang perintah sudah berangkat. Telegram belum dibangun — itu FS-47.</p>
<p>Prasyarat lab: <strong>FS-45</strong> (halaman grafik sudah pernah terbuka), <strong>FS-42</strong> (pintu POST sudah pernah terbuka), <strong>FS-35</strong> (relay lab sebelumnya, tanpa kabel baru), dan MQTTX + Mosquitto dari FS-33. FS-41 MariaDB <strong>tidak wajib</strong>. ESP32 <strong>boleh menyala</strong>, dan <strong>boleh dicabut</strong>. Tidak ada kabel baru, tidak ada Upload, <strong>Bukan AC 220V</strong>.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>MQTTX Connected ke <code>127.0.0.1:1883</code> dan sudah Subscribe topic command.</li>
<li>PowerShell menampilkan <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>Bilah alamat browser adalah <code>http://127.0.0.1:5000</code> — bukan <code>file://</code>.</li>
<li>Halaman menampilkan tombol ON dan OFF.</li>
<li>Setelah klik ON, teks status adalah <code>Perintah terkirim.</code></li>
<li>Teks sakelar adalah <code>Sakelar: ON</code>.</li>
<li>MQTTX menampilkan JSON <code>{"device_id":"esp32-meja-01","relay":"on"}</code>.</li>
<li>Berkas <code>stasiun.db</code> masih ada.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tombol di halaman HTML, POST ke Mosquitto, GET status dari SQLite. Belum Telegram, belum MySQL, belum membuka port 5000 ke internet. Bukti cukup = teks halaman + pesan command di MQTTX. Klik relay fisik adalah bonus jika papan masih menyala. <code>flask==3.1.3</code> sudah dari FS-42. Grafik Chart.js kemarin <strong>boleh tetap</strong>.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Tombol ON/OFF</strong> — dua tombol di halaman yang mengirim perintah sakelar.</li>
<li><strong>POST /command</strong> — pintu kirim. Flask meneruskannya ke topic MQTT command.</li>
<li><strong>GET /status</strong> — pintu baca perintah terakhir, supaya tulisan sakelar tidak kosong setelah refresh.</li>
<li><strong>Sakelar: ON</strong> — teks status di halaman setelah perintah on tersimpan.</li>
<li><strong>Perintah terkirim.</strong> — teks kunci hari ini. Artinya JSON <code>"ok": true</code> sudah kembali.</li>
<li><strong>sedang</strong> — kunci di JavaScript supaya klik kedua tidak mengirim ulang sebelum jawaban datang.</li>
<li><strong>Topic command</strong> — alamat MQTT <code>kodingindonesia/fsiot/esp32-meja-01/command</code>.</li>
<li><strong>NC/COM/NO</strong> — kaki relay. Hari ini dibiarkan kosong. <strong>Bukan AC 220V</strong>.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p>Buka File Explorer ke folder lab sebelum Notepad. Jangan ketik perintah Python dulu.</p>
<p><strong>Jangan dipakai hari ini:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC 220V, <code>file://</code>, membuka port 5000 ke internet, mengubah ExecutionPolicy, pip <code>flask-cors</code>, npm, Telegram, atau <code>uji_perintah.py</code> sebagai jalur lulus. Node-RED boleh tetap terbuka.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa tombol di halaman</h2>
HTML
            .$why.<<<'HTML'
<p>Kemarin kamu melihat tren. Melihat tidak menggerakkan sakelar. Hari ini browser yang menekan pintu POST, sama seperti <code>uji_perintah.py</code> di FS-42, tetapi tanpa ganti jendela PowerShell.</p>
<p>Gudang tetap SQLite <code>stasiun.db</code>. Jangan menunggu MariaDB. FS-41 tetap opsional. Telegram ditunda FS-47.</p>

<h2>Nyalakan MQTTX, langganan topic command</h2>
HTML
            .$mqttx.<<<'HTML'
<p>Buka MQTTX. Connect ke <code>127.0.0.1:1883</code>. Subscribe:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/command</code></pre>
<p><strong>Hasil yang dicari:</strong> status Connected, langganan command terlihat, daftar pesan siap diisi. Flask yang akan mengisi. Jangan tekan Publish.</p>
<p>Jika Mosquitto belum jalan, nyalakan dulu seperti FS-33. Tanpa broker, tombol akan menulis <code>Broker belum terbuka di 127.0.0.1:1883</code>.</p>

<h2>Flask membuka pintu perintah</h2>
HTML
            .$flaskServe.$post.<<<'HTML'
<p><code>requirements.txt</code> tetap mengunci <code>flask==3.1.3</code> dan <code>paho-mqtt==2.1.0</code> seperti FS-42. Jangan pip ke Python global. Kalau Flask sudah terpasang, tidak perlu pip ulang. Jangan tambah <code>flask-cors</code>.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>. Folder <code>.venv</code>, berkas <code>stasiun.db</code>, dan <code>pintu_stasiun.py</code> dari lab sebelumnya harus sudah ada. Jika <code>stasiun.db</code> belum ada, ulangi FS-40 dulu.</p>
<p><strong>Buka dulu Notepad.</strong> Ganti isi <code>pintu_stasiun.py</code> dengan kode di bawah. Save As, All files, folder <code>Documents\fsiot-fs39</code>. GET <code>/</code> mengirim <code>dashboard.html</code>. GET <code>/status</code> mengirim perintah terakhir. POST <code>/command</code> mengirim MQTT lalu menyimpan baris di tabel <code>commands</code>. GET <code>/telemetry</code> dan GET <code>/history?hours=1</code> tetap ada. Saringan <code>?device_id=</code> dari FS-43 tetap ada.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>Jika Flask hilang dari venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Tulis dashboard.html dengan tombol</h2>
<p><strong>Buka dulu Notepad.</strong> Ganti isi <code>dashboard.html</code>. File → Save As, All files, folder lab. Jangan Save sebagai <code>.txt</code>.</p>
<pre><code class="language-html">
HTML
            .$dashboard.<<<'HTML'
</code></pre>
<p>HTML menyusun judul, grafik kemarin, lalu dua tombol. <code>fetch("/command")</code> mengirim JSON <code>relay</code> <code>on</code> atau <code>off</code>. <code>fetch("/status")</code> menulis sakelar saat halaman dibuka. Yang dikunci adalah teks <code>Perintah terkirim.</code> dan <code>Sakelar: ON</code>.</p>

<h2>Jaga tombol: jangan dobel kirim</h2>
HTML
            .$double.<<<'HTML'
<p>Tanpa kunci, dua klik cepat mengirim dua pesan MQTT. Variabel <code>sedang</code> menolak klik kedua. Tombol <code>disabled</code> selama fetch. Teks sementara adalah <code>Mengirim…</code>. Setelah JSON kembali, tombol dibuka lagi.</p>
<p>Itu cukup untuk lab meja. Jangan menambah antrian rumit hari ini.</p>

<h2>Baca status setelah klik</h2>
HTML
            .$status.<<<'HTML'
<p>Kalau halaman di-refresh, tulisan sakelar tidak boleh selalu kosong. GET <code>/status</code> membaca baris terakhir tabel <code>commands</code> di SQLite. Jika belum ada perintah, teksnya <code>Sakelar: belum diketahui</code>.</p>
<p>Jangan menunggu MySQL. Gudang tetap <code>stasiun.db</code>.</p>

<h2>Jalankan Flask, klik ON di browser</h2>
HTML
            .$browser.<<<'HTML'
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<p>Tutup Flask lama jika masih jalan, lalu:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code> lalu <code>Buka http://127.0.0.1:5000</code> lalu <code>POST http://127.0.0.1:5000/command</code> lalu <code>GET  http://127.0.0.1:5000/status</code>. Jendela ini <strong>tetap terbuka</strong>. Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>.</p>
<p><strong>Buka browser</strong> di tab baru. Ketik alamat ini, lalu Enter:</p>
<pre><code>http://127.0.0.1:5000</code></pre>
<p><strong>Hasil yang dicari:</strong> judul Stasiun meja, tombol ON dan OFF. Klik ON. Teks <code>Perintah terkirim.</code> lalu <code>Sakelar: ON</code>. Ini halaman HTML, bukan JSON mentah. <strong>Jangan buka berkas HTML lewat <code>file://</code>.</strong></p>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python pintu_stasiun.py</code>.</p>

<h2>MQTTX menampilkan perintah</h2>
<p>Setelah klik ON, jendela MQTTX mendapat pesan baru di topic command. Isi yang dikunci:</p>
<pre><code>{"device_id":"esp32-meja-01","relay":"on"}</code></pre>
<p>Jika ESP32 masih menjalankan firmware perintah dari lab sebelumnya, relay mengikuti. Jika papan dicabut, MQTTX tetap cukup untuk lulus hari ini. Biarkan NC/COM/NO kosong. <strong>Bukan AC 220V.</strong></p>

<h2>Bonus: klik OFF</h2>
<p>Tidak wajib. Jika ON sudah tampil di halaman dan di MQTTX, lab utama selesai. Bonus: klik OFF. Halaman menulis <code>Sakelar: OFF</code>. MQTTX menampilkan <code>"relay":"off"</code>.</p>
<p>Jangan memakai <code>uji_perintah.py</code> sebagai bukti hari ini. Tombol menggantikan script itu.</p>

<h2>Jika tombol tidak merespons</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>MQTTX belum Connected.</strong> Connect dulu, lalu Subscribe topic command. Tanpa itu, pesan tidak kelihatan.</li>
<li><strong>Flask belum terbuka.</strong> Jendela harus menampilkan <code>Pintu stasiun terbuka</code>. Jangan ditutup sebelum tombol dicoba.</li>
<li><strong>Masih file://.</strong> Tutup tab. Ketik <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Broker 1883 tertutup.</strong> Halaman menulis <code>Broker belum terbuka di 127.0.0.1:1883</code>. Nyalakan Mosquitto.</li>
<li><strong>dashboard.html belum di folder yang sama.</strong> Flask mencari berkas di samping <code>pintu_stasiun.py</code>.</li>
</ol>

<h2 id="fsiot-control-checklist">Checklist sebelum FS-47</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-control-checklist-items">
<li>Saya tidak membuka <code>dashboard.html</code> lewat <code>file://</code>.</li>
<li>MQTTX Connected ke 127.0.0.1:1883 dan Subscribe topic command.</li>
<li>PowerShell menampilkan Pintu stasiun terbuka di port 5000.</li>
<li>Bilah alamat browser adalah <code>http://127.0.0.1:5000</code>.</li>
<li>Halaman menampilkan tombol ON dan OFF.</li>
<li>Setelah klik ON, teks status adalah <code>Perintah terkirim.</code></li>
<li>Teks sakelar adalah <code>Sakelar: ON</code>.</li>
<li>MQTTX menampilkan JSON relay on.</li>
<li>Saya tidak mengubah ExecutionPolicy.</li>
<li>Saya tidak memakai MySQL atau AC 220V hari ini.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: MQTTX → tombol ON → POST <code>/command</code> → <code>Perintah terkirim.</code> → JSON di MQTTX. Pada FS-47, stasiun mulai mengirim peringatan ke Telegram. MariaDB tetap opsional.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Membuka file://.</strong> Fetch ke Flask ditolak. Pakai <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Menunggu MySQL.</strong> Gudang tetap SQLite <code>stasiun.db</code>.</li>
<li><strong>Dobel klik tanpa kunci.</strong> Dua perintah berangkat. Lab mengunci tombol selama <code>sedang</code>.</li>
<li><strong>UI tidak sync.</strong> Refresh mengosongkan tulisan. Pakai GET <code>/status</code>.</li>
<li><strong>Memakai uji_perintah.py sebagai bukti.</strong> Hari ini buktinya tombol di halaman.</li>
<li><strong>Mengubah ExecutionPolicy.</strong> Tetap pakai <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Membangun bot Telegram hari ini.</strong> Ditunda ke FS-47.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa MQTTX wajib hari ini?</h3>
<p>Supaya perintah kelihatan meski ESP32 dicabut. Halaman bilang terkirim; MQTTX bilang broker menerimanya.</p>
<h3>ESP32 wajib menyala?</h3>
<p>Tidak. JSON di MQTTX cukup. Klik relay fisik adalah bonus. <strong>Bukan AC 220V.</strong></p>
<h3>Wajib MySQL?</h3>
<p>Tidak. SQLite cukup. FS-41 tetap opsional.</p>
<h3>Kenapa tombol dikunci?</h3>
<p>Supaya satu klik tidak menjadi dua pesan. Variabel <code>sedang</code> menahan klik kedua.</p>
<h3>Apakah uji_perintah.py hari ini?</h3>
<p>Tidak sebagai jalur lulus. Pintu <code>/command</code> sama; yang baru adalah tombol di halaman.</p>
<h3>Wajib klik relay fisik?</h3>
<p>Tidak. Biarkan NC/COM/NO kosong. Tidak ada kabel baru.</p>
<h3>Kenapa GET /status?</h3>
<p>Supaya setelah refresh, <code>Sakelar: ON</code> masih bisa dibaca dari SQLite.</p>
<h3>Apakah Telegram hari ini?</h3>
<p>Tidak. Peringatan ke HP adalah FS-47.</p>
<h3>Kenapa jangan file://?</h3>
<p>Karena itu bukan server. Halaman, JSON, dan tombol harus satu origin Flask.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API" target="_blank" rel="noopener noreferrer">MDN Fetch API</a></li>
<li><a href="https://flask.palletsprojects.com/en/stable/quickstart/#about-responses" target="_blank" rel="noopener noreferrer">Flask JSON responses</a> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://eclipse.dev/paho/files/paho.mqtt.python/html/" target="_blank" rel="noopener noreferrer">Eclipse Paho MQTT Python</a> (EPL-2.0)</li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://www.chartjs.org/docs/latest/getting-started/installation.html" target="_blank" rel="noopener noreferrer">Chart.js installation</a> (MIT) — grafik kemarin boleh tetap</li>
<li>Diagram urutan tools, grafik versus tombol, POST perintah, kunci dobel klik, GET status, Flask menyajikan, skema periksa — Koding Indonesia (FS-46). Ilustrasi jendela tombol dan MQTTX — Koding Indonesia (FS-46).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> tombol ON/OFF sudah terbuka di <code>http://127.0.0.1:5000</code>. POST <code>/command</code> ke Mosquitto, status dari SQLite. Pada <strong>FS-47</strong>, stasiun mulai mengirim peringatan ke Telegram. Jangan <code>file://</code>. MariaDB tetap opsional.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $dashboard = htmlspecialchars($this->dashboard(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs46-tools-order.png', 'Five-step order: browser, MQTTX Subscribe command, Notepad, PowerShell Flask, browser click ON', '<strong>Desk order (five steps):</strong> browser → MQTTX Connect <code>127.0.0.1:1883</code> then Subscribe the command topic → Notepad writes <code>dashboard.html</code> and <code>pintu_stasiun.py</code> → PowerShell Flask → browser clicks ON until <code>Perintah terkirim.</code> Diagram by Koding Indonesia (FS-46).');
        $why = $this->figure('fs46-why-buttons.png', 'Three boxes: FS-45 chart, ON OFF buttons, status Sakelar ON Perintah terkirim', '<strong>Yesterday a trend line. Today switch buttons.</strong> Read left to right: the chart → POST <code>/command</code> → status <code>Sakelar: ON</code>. Diagram by Koding Indonesia (FS-46).');
        $post = $this->figure('fs46-post-flow.png', 'Left-to-right flow: ON button, POST command, Mosquitto 1883, MQTTX relay on', '<strong>Main figure — click the button, Flask forwards the command.</strong> Read left to right: ON → POST <code>/command</code> → broker → MQTTX. Diagram by Koding Indonesia (FS-46).');
        $double = $this->figure('fs46-double-submit.png', 'Three boxes: click ON once lock buttons, fetch POST sending, done unlock buttons', '<strong>Guard the buttons: one click, one send.</strong> Read left to right: click → lock → wait for the answer → unlock. Diagram by Koding Indonesia (FS-46).');
        $status = $this->figure('fs46-status.png', 'Left-to-right flow: open the page, GET status, JSON relay, write Sakelar ON', '<strong>Read status so the UI does not fall behind.</strong> Read left to right: open the page → GET <code>/status</code> → write <code>Sakelar: ON</code>. Diagram by Koding Indonesia (FS-46).');
        $flaskServe = $this->figure('fs46-flask-serve.png', 'Left-to-right flow: GET slash, GET status, POST command, MQTT, text Perintah terkirim', '<strong>Flask serves the page, the status, and the command.</strong> Read left to right: GET <code>/</code> → GET <code>/status</code> → POST <code>/command</code> → MQTT. Diagram by Koding Indonesia (FS-46).');
        $browser = $this->figure('fs46-browser-panel.png', 'Browser window illustration showing title Stasiun meja, Sakelar ON, ON OFF buttons, status Perintah terkirim', '<strong>The browser is already showing switch buttons.</strong> The lock is the address <code>http://127.0.0.1:5000</code> and the text <code>Perintah terkirim.</code> Yesterday’s chart may stay. Illustration by Koding Indonesia (FS-46), modelled on a browser window. The official window is not used as-is.');
        $mqttx = $this->figure('fs46-mqttx.png', 'MQTTX illustration Connected to 127.0.0.1:1883 showing JSON relay on on the command topic', '<strong>MQTTX is already showing the relay command.</strong> Connect first, then Subscribe. Do not press Publish. Illustration by Koding Indonesia (FS-46), modelled on <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0). The official window screenshot is not used as-is.');
        $troubleshooting = $this->figure('fs46-troubleshooting.png', 'Four checks: MQTTX, Flask, file versus http address, broker 1883', '<strong>Helper schematic.</strong> MQTTX Connected. Flask uses <code>127.0.0.1:5000</code>. Do not use <code>file://</code>. Diagram by Koding Indonesia (FS-46).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not type Python commands yet.'],
            ['title' => 'Open MQTTX', 'text' => 'Connect to <code>127.0.0.1:1883</code>. Press Subscribe on topic <code>kodingindonesia/fsiot/esp32-meja-01/command</code>. Do not Publish yet.'],
            ['title' => 'Open Notepad, write the files', 'text' => 'Update <code>dashboard.html</code> and <code>pintu_stasiun.py</code>. All files, not <code>.txt</code>.'],
            ['title' => 'Open PowerShell, run Flask', 'text' => 'Start → type PowerShell. You do not need <em>Run as administrator</em>. Run Flask. Leave the Flask window open.'],
            ['title' => 'Open a browser, click ON', 'text' => 'New tab: <code>http://127.0.0.1:5000</code> — not <code>file://</code>. Click ON. Status <code>Perintah terkirim.</code>'],
        ], '<strong>How to test today:</strong> success = the browser shows <code>Perintah terkirim.</code> plus <code>Sakelar: ON</code>, and MQTTX shows JSON <code>relay":"on"</code>. The ESP32 may be on, but it is not required.');

        return <<<'HTML'
<h2>Introduction — buttons, not only a chart</h2>
<p><strong>FS-46 / #116 (this article)</strong> is the control-panel lab. Yesterday the page already showed a temperature trend line. Today the job is different: <strong>turn the switch on and off from the browser</strong>, so the command is no longer typed in a script.</p>
<p><strong>In short:</strong> put ON/OFF buttons in <code>dashboard.html</code>, send POST <code>/command</code>, read GET <code>/status</code>, until the text <code>Perintah terkirim.</code> and <code>Sakelar: ON</code> appear at <code>http://127.0.0.1:5000</code>.</p>
<p><strong>Analogy:</strong> the chart is a rear-view mirror. The buttons are the handlebars: you move the switch, then the page says the command has left. Telegram is not built yet — that is FS-47.</p>
<p>Lab prerequisites: <strong>FS-45</strong> (the chart page has opened before), <strong>FS-42</strong> (the POST door has opened before), <strong>FS-35</strong> (the earlier relay lab, with no new cables), and MQTTX + Mosquitto from FS-33. FS-41 MariaDB is <strong>not required</strong>. The ESP32 <strong>may stay on</strong>, and <strong>may be unplugged</strong>. No new cables, no Upload, <strong>Not AC mains</strong>.</p>

<h2>Expected outcome</h2>
<ul>
<li>MQTTX is Connected to <code>127.0.0.1:1883</code> and has Subscribed to the command topic.</li>
<li>PowerShell shows <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>The browser address bar is <code>http://127.0.0.1:5000</code> — not <code>file://</code>.</li>
<li>The page shows ON and OFF buttons.</li>
<li>After clicking ON, the status text is <code>Perintah terkirim.</code></li>
<li>The switch text is <code>Sakelar: ON</code>.</li>
<li>MQTTX shows JSON <code>{"device_id":"esp32-meja-01","relay":"on"}</code>.</li>
<li>The <code>stasiun.db</code> file is still there.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> buttons on the HTML page, POST to Mosquitto, GET status from SQLite. No Telegram, no MySQL, no opening port 5000 to the internet. Enough proof = page text + a command message in MQTTX. A physical relay click is a bonus if the board is still on. <code>flask==3.1.3</code> already comes from FS-42. Yesterday’s Chart.js graph <strong>may stay</strong>.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>ON/OFF buttons</strong> — two buttons on the page that send a switch command.</li>
<li><strong>POST /command</strong> — the send door. Flask forwards it to the MQTT command topic.</li>
<li><strong>GET /status</strong> — the read door for the last command, so the switch label is not empty after a refresh.</li>
<li><strong>Sakelar: ON</strong> — the status text on the page after an on command is stored.</li>
<li><strong>Perintah terkirim.</strong> — today’s lock text. It means JSON <code>"ok": true</code> has come back.</li>
<li><strong>sedang</strong> — a JavaScript lock so a second click does not send again before the answer arrives.</li>
<li><strong>Command topic</strong> — the MQTT address <code>kodingindonesia/fsiot/esp32-meja-01/command</code>.</li>
<li><strong>NC/COM/NO</strong> — relay pins. Leave them empty today. <strong>Not AC mains</strong>.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p>Open File Explorer to the lab folder before Notepad. Do not type Python commands yet.</p>
<p><strong>Do not use today:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC mains, <code>file://</code>, opening port 5000 to the internet, changing ExecutionPolicy, pip <code>flask-cors</code>, npm, Telegram, or <code>uji_perintah.py</code> as the pass path. Node-RED may stay open.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen, so the text around it stays readable.</p>

<h2>Why buttons on the page</h2>
HTML
            .$why.<<<'HTML'
<p>Yesterday you watched a trend. Watching does not move a switch. Today the browser knocks on the POST door, same as <code>uji_perintah.py</code> in FS-42, but without switching to a PowerShell window.</p>
<p>The store stays SQLite <code>stasiun.db</code>. Do not wait for MariaDB. FS-41 stays optional. Telegram waits for FS-47.</p>

<h2>Start MQTTX, subscribe to the command topic</h2>
HTML
            .$mqttx.<<<'HTML'
<p>Open MQTTX. Connect to <code>127.0.0.1:1883</code>. Subscribe:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/command</code></pre>
<p><strong>What you want:</strong> status Connected, the command subscription visible, the message list ready to fill. Flask will fill it. Do not press Publish.</p>
<p>If Mosquitto is not running, start it first like FS-33. Without the broker, the button writes <code>Broker belum terbuka di 127.0.0.1:1883</code>.</p>

<h2>Flask opens the command door</h2>
HTML
            .$flaskServe.$post.<<<'HTML'
<p><code>requirements.txt</code> still pins <code>flask==3.1.3</code> and <code>paho-mqtt==2.1.0</code> like FS-42. Do not pip into global Python. If Flask is already installed, you do not need to pip again. Do not add <code>flask-cors</code>.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Open File Explorer first</strong>, go to <code>Documents\fsiot-fs39</code>. The <code>.venv</code> folder, the <code>stasiun.db</code> file, and <code>pintu_stasiun.py</code> from the earlier lab must already be there. If <code>stasiun.db</code> is missing, repeat FS-40 first.</p>
<p><strong>Open Notepad first.</strong> Replace <code>pintu_stasiun.py</code> with the code below. Save As, All files, folder <code>Documents\fsiot-fs39</code>. GET <code>/</code> sends <code>dashboard.html</code>. GET <code>/status</code> sends the last command. POST <code>/command</code> sends MQTT then stores a row in the <code>commands</code> table. GET <code>/telemetry</code> and GET <code>/history?hours=1</code> stay. The <code>?device_id=</code> filter from FS-43 stays.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>If Flask is missing from the venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Write dashboard.html with buttons</h2>
<p><strong>Open Notepad first.</strong> Replace <code>dashboard.html</code>. File → Save As, All files, lab folder. Do not Save as <code>.txt</code>.</p>
<pre><code class="language-html">
HTML
            .$dashboard.<<<'HTML'
</code></pre>
<p>HTML lays out the title, yesterday’s chart, then two buttons. <code>fetch("/command")</code> sends JSON <code>relay</code> <code>on</code> or <code>off</code>. <code>fetch("/status")</code> writes the switch when the page opens. The lock is the text <code>Perintah terkirim.</code> and <code>Sakelar: ON</code>.</p>

<h2>Guard the buttons: do not double-send</h2>
HTML
            .$double.<<<'HTML'
<p>Without a lock, two fast clicks send two MQTT messages. The <code>sedang</code> variable rejects the second click. The buttons are <code>disabled</code> during fetch. The temporary text is <code>Mengirim…</code>. After JSON returns, the buttons unlock.</p>
<p>That is enough for a desk lab. Do not add a complicated queue today.</p>

<h2>Read status after a click</h2>
HTML
            .$status.<<<'HTML'
<p>If the page is refreshed, the switch label must not always go empty. GET <code>/status</code> reads the last row of the <code>commands</code> table in SQLite. If there is no command yet, the text is <code>Sakelar: belum diketahui</code>.</p>
<p>Do not wait for MySQL. The store stays <code>stasiun.db</code>.</p>

<h2>Run Flask, click ON in the browser</h2>
HTML
            .$browser.<<<'HTML'
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste a command:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<p>Close the old Flask window if it is still running, then:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>What you want:</strong> <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code> then <code>Buka http://127.0.0.1:5000</code> then <code>POST http://127.0.0.1:5000/command</code> then <code>GET  http://127.0.0.1:5000/status</code>. Leave this window <strong>open</strong>. If <code>.\.venv\Scripts\Activate.ps1</code> is rejected, <strong>do not change ExecutionPolicy</strong>.</p>
<p><strong>Open a browser</strong> in a new tab. Type this address, then Enter:</p>
<pre><code>http://127.0.0.1:5000</code></pre>
<p><strong>What you want:</strong> the title Stasiun meja, ON and OFF buttons. Click ON. The text <code>Perintah terkirim.</code> then <code>Sakelar: ON</code>. This is an HTML page, not raw JSON. <strong>Do not open the HTML file through <code>file://</code>.</strong></p>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python pintu_stasiun.py</code>.</p>

<h2>MQTTX shows the command</h2>
<p>After you click ON, the MQTTX window gets a new message on the command topic. The locked payload is:</p>
<pre><code>{"device_id":"esp32-meja-01","relay":"on"}</code></pre>
<p>If the ESP32 is still running the command firmware from the earlier lab, the relay follows. If the board is unplugged, MQTTX is still enough to pass today. Leave NC/COM/NO empty. <strong>Not AC mains.</strong></p>

<h2>Bonus: click OFF</h2>
<p>Not required. If ON already shows on the page and in MQTTX, the main lab is done. Bonus: click OFF. The page writes <code>Sakelar: OFF</code>. MQTTX shows <code>"relay":"off"</code>.</p>
<p>Do not use <code>uji_perintah.py</code> as today’s proof. The buttons replace that script.</p>

<h2>If the buttons do not respond</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>MQTTX is not Connected yet.</strong> Connect first, then Subscribe to the command topic. Without that, the message is invisible.</li>
<li><strong>Flask is not open yet.</strong> The window must show <code>Pintu stasiun terbuka</code>. Do not close it before you try the buttons.</li>
<li><strong>Still file://.</strong> Close the tab. Type <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Broker 1883 is closed.</strong> The page writes <code>Broker belum terbuka di 127.0.0.1:1883</code>. Start Mosquitto.</li>
<li><strong>dashboard.html is not in the same folder.</strong> Flask looks for the file next to <code>pintu_stasiun.py</code>.</li>
</ol>

<h2 id="fsiot-control-checklist">Checklist before FS-47</h2>
<p>Tick after you have actually done each item. Target: <strong>10/10</strong>. Progress stays in this device’s browser and is not sent to the server.</p>
<ul id="fsiot-control-checklist-items">
<li>I did not open <code>dashboard.html</code> through <code>file://</code>.</li>
<li>MQTTX is Connected to 127.0.0.1:1883 and Subscribed to the command topic.</li>
<li>PowerShell shows Pintu stasiun terbuka on port 5000.</li>
<li>The browser address bar is <code>http://127.0.0.1:5000</code>.</li>
<li>The page shows ON and OFF buttons.</li>
<li>After clicking ON, the status text is <code>Perintah terkirim.</code></li>
<li>The switch text is <code>Sakelar: ON</code>.</li>
<li>MQTTX shows the relay on JSON.</li>
<li>I did not change ExecutionPolicy.</li>
<li>I did not use MySQL or AC mains today.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: MQTTX → ON button → POST <code>/command</code> → <code>Perintah terkirim.</code> → JSON in MQTTX. In FS-47, the station starts sending a warning to Telegram. MariaDB stays optional.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Opening file://.</strong> Fetch to Flask is blocked. Use <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Waiting for MySQL.</strong> The store stays SQLite <code>stasiun.db</code>.</li>
<li><strong>Double-clicking without a lock.</strong> Two commands leave. The lab locks the buttons while <code>sedang</code> is true.</li>
<li><strong>UI out of sync.</strong> A refresh clears the label. Use GET <code>/status</code>.</li>
<li><strong>Using uji_perintah.py as proof.</strong> Today’s proof is the button on the page.</li>
<li><strong>Changing ExecutionPolicy.</strong> Keep using <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Building a Telegram bot today.</strong> That waits for FS-47.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why is MQTTX required today?</h3>
<p>So the command is visible even if the ESP32 is unplugged. The page says it was sent; MQTTX says the broker received it.</p>
<h3>Must the ESP32 stay on?</h3>
<p>No. JSON in MQTTX is enough. A physical relay click is a bonus. <strong>Not AC mains</strong>.</p>
<h3>Is MySQL required?</h3>
<p>No. SQLite is enough. FS-41 stays optional.</p>
<h3>Why lock the buttons?</h3>
<p>So one click does not become two messages. The <code>sedang</code> variable holds the second click.</p>
<h3>Is uji_perintah.py used today?</h3>
<p>Not as the pass path. The <code>/command</code> door is the same; what is new is the button on the page.</p>
<h3>Must the physical relay click?</h3>
<p>No. Leave NC/COM/NO empty. No new cables.</p>
<h3>Why GET /status?</h3>
<p>So after a refresh, <code>Sakelar: ON</code> can still be read from SQLite.</p>
<h3>Is Telegram today?</h3>
<p>No. Phone alerts are FS-47.</p>
<h3>Why not file://?</h3>
<p>Because that is not a server. The page, the JSON, and the buttons must share one Flask origin.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API" target="_blank" rel="noopener noreferrer">MDN Fetch API</a></li>
<li><a href="https://flask.palletsprojects.com/en/stable/quickstart/#about-responses" target="_blank" rel="noopener noreferrer">Flask JSON responses</a> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://eclipse.dev/paho/files/paho.mqtt.python/html/" target="_blank" rel="noopener noreferrer">Eclipse Paho MQTT Python</a> (EPL-2.0)</li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://www.chartjs.org/docs/latest/getting-started/installation.html" target="_blank" rel="noopener noreferrer">Chart.js installation</a> (MIT) — yesterday’s chart may stay</li>
<li>Diagrams for tool order, chart versus buttons, POST command, double-click lock, GET status, Flask serving, helper schematic — Koding Indonesia (FS-46). Button window and MQTTX illustrations — Koding Indonesia (FS-46).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> ON/OFF buttons are already open at <code>http://127.0.0.1:5000</code>. POST <code>/command</code> to Mosquitto, status from SQLite. In <strong>FS-47</strong>, the station starts sending a warning to Telegram. Do not use <code>file://</code>. MariaDB stays optional.</p>
HTML;
    }
}
