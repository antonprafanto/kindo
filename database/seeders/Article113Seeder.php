<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article113Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-device-id-dua-stasiun';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'python', 'flask', 'mqtt', 'sqlite', 'esp32'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Beri nama dua stasiun lalu saring datanya di REST Flask',
            'title_en' => 'Name two stations then filter their data in REST Flask',
            'excerpt' => 'FS-43 / #113: dua device_id di SQLite, GET ?device_id=esp32-meja-02 sampai JSON jumlah 5. Satu papan cukup. Belum dashboard HTML.',
            'excerpt_en' => 'FS-43 / #113: two device_id values in SQLite, GET ?device_id=esp32-meja-02 until JSON shows 5 rows. One board is enough. No HTML dashboard.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Saring Dua Stasiun lewat device_id — FS-43',
            'seo_title_en' => 'Filter Two Stations by device_id — FS-43',
            'seo_description' => 'Lab pemula: isi 5 baris meja-02, saring GET Flask, perintah MQTT ke nama yang sama. Bukan Username MQTTX, bukan dashboard.',
            'seo_description_en' => 'A first lab: add 5 meja-02 rows, filter Flask GET, MQTT command to the matching name. Not the MQTTX Username, no dashboard.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'flask', 'mqtt', 'sqlite', 'esp32'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs43-cover-device.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs43-cover-device.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs43-cover-device.webp',
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
            'from datetime import datetime, timezone',
            'from pathlib import Path',
            '',
            'FOLDER = Path(__file__).resolve().parent',
            'DB_PATH = FOLDER / "stasiun.db"',
            '',
            'if not DB_PATH.exists():',
            '    print("Berkas stasiun.db belum ada. Ulangi FS-40 dulu.")',
            '    raise SystemExit(1)',
            '',
            'now = datetime.now(timezone.utc).astimezone().isoformat(timespec="seconds")',
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
            '    db.execute("DELETE FROM telemetry WHERE device_id = ?", ("esp32-meja-02",))',
            '    for index in range(5):',
            '        db.execute(',
            '            """',
            '            INSERT INTO telemetry (received_at, device_id, temperature_c, humidity_pct, topic, payload)',
            '            VALUES (?, ?, ?, ?, ?, ?)',
            '            """',
            '            ,',
            '            (',
            '                now,',
            '                "esp32-meja-02",',
            '                round(24.0 + index * 0.3, 1),',
            '                round(55.0 + index, 1),',
            '                "kodingindonesia/fsiot/esp32-meja-02/telemetry",',
            '                \'{"device_id":"esp32-meja-02"}\',',
            '            ),',
            '        )',
            '    n1 = db.execute(',
            '        "SELECT COUNT(*) FROM telemetry WHERE device_id = ?",',
            '        ("esp32-meja-01",),',
            '    ).fetchone()[0]',
            '    n2 = db.execute(',
            '        "SELECT COUNT(*) FROM telemetry WHERE device_id = ?",',
            '        ("esp32-meja-02",),',
            '    ).fetchone()[0]',
            '    print("Baris meja-01:", n1)',
            '    print("Baris meja-02:", n2)',
            '    print("5 baris meja-02 siap.")',
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
            'from flask import Flask, jsonify, request',
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
            '        client_id="fsiot-fs43-pintu",',
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
            '    print("GET  http://127.0.0.1:5000/telemetry")',
            '    print("GET  http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02")',
            '    app.run(host=HOST, port=PORT, debug=False)',
        ]);
    }

    private function uji(): string
    {
        return implode("\n", [
            'import json',
            'from urllib.error import URLError',
            'from urllib.request import Request, urlopen',
            '',
            'URL = "http://127.0.0.1:5000/command"',
            'payload = {"device_id": "esp32-meja-02", "relay": "on"}',
            'body = json.dumps(payload).encode("utf-8")',
            'req = Request(',
            '    URL,',
            '    data=body,',
            '    headers={"Content-Type": "application/json"},',
            '    method="POST",',
            ')',
            'try:',
            '    with urlopen(req, timeout=8) as resp:',
            '        print(resp.read().decode("utf-8"))',
            'except URLError as error:',
            '    print("Pintu Flask belum terbuka di 127.0.0.1:5000")',
            '    print(error)',
            '    raise SystemExit(1) from error',
            'print("Perintah terkirim.")',
        ]);
    }

    private function kirim(): string
    {
        return implode("\n", [
            'import json',
            '',
            'import paho.mqtt.client as mqtt',
            '',
            'BROKER = "127.0.0.1"',
            'MQTT_PORT = 1883',
            'IDS = ["esp32-meja-01", "esp32-meja-02"]',
            '',
            'client = mqtt.Client(',
            '    callback_api_version=mqtt.CallbackAPIVersion.VERSION2,',
            '    client_id="fsiot-fs43-kirim",',
            ')',
            'try:',
            '    client.connect(BROKER, MQTT_PORT, keepalive=60)',
            'except OSError as error:',
            '    print("Broker belum terbuka di 127.0.0.1:1883")',
            '    print(error)',
            '    raise SystemExit(1) from error',
            'for device_id in IDS:',
            '    topic = f"kodingindonesia/fsiot/{device_id}/telemetry"',
            '    payload = json.dumps({"device_id": device_id, "temperature_c": 27.0, "humidity_pct": 60})',
            '    client.publish(topic, payload)',
            '    print(topic)',
            'client.disconnect()',
            'print("Dua nama terkirim ke MQTTX.")',
        ]);
    }

    private function body(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $isi = htmlspecialchars($this->isi(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $uji = htmlspecialchars($this->uji(), ENT_QUOTES, 'UTF-8');
        $kirim = htmlspecialchars($this->kirim(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs43-tools-order.png', 'Urutan lima langkah: browser, MQTTX dua topic telemetry, Notepad, PowerShell, browser GET saring', '<strong>Urutan meja kerja (lima langkah):</strong> browser → MQTTX Connect 127.0.0.1:1883 lalu Subscribe dua topic telemetry → Notepad menulis berkas → PowerShell mengisi SQLite lalu Flask → browser GET <code>?device_id=</code>. Diagram buatan Koding Indonesia (FS-43).');
        $why = $this->figure('fs43-why-id.png', 'Perbandingan data campur tanpa nama dan dua stasiun bernama yang bisa disaring', '<strong>Tanpa nama, data tertumpuk. Dengan nama, bisa disaring.</strong> Identitas hari ini adalah <code>device_id</code>, bukan Username MQTTX. Diagram buatan Koding Indonesia (FS-43).');
        $topic = $this->figure('fs43-topic.png', 'Alur kiri ke kanan: kodingindonesia, fsiot, device_id, telemetry atau command', '<strong>Baca topic kiri ke kanan.</strong> Nama stasiun ada di tengah: <code>esp32-meja-01</code> atau <code>esp32-meja-02</code>. Diagram buatan Koding Indonesia (FS-43).');
        $mqttx = $this->figure('fs43-mqttx.png', 'Ilustrasi MQTTX Connected ke 127.0.0.1:1883 dengan dua langganan topic telemetry', '<strong>MQTTX sudah berlangganan dua nama.</strong> Connect dulu, baru Subscribe dua kali. Jangan Publish dulu. Ilustrasi buatan Koding Indonesia (FS-43), meniru <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0). Tangkapan layar resmi tidak dipakai utuh.');
        $names = $this->figure('fs43-two-names.png', 'Satu papan ESP32 dengan dua nama stasiun esp32-meja-01 dan esp32-meja-02', '<strong>Satu papan cukup.</strong> Yang diganti adalah nama di JSON dan topic, bukan urutan kaki. Diagram buatan Koding Indonesia (FS-43). Contoh rupa papan ada di Sumber (Wikimedia Commons, CC0) — foto GPIO tidak dipakai di sini agar tidak disalin salah.');
        $filter = $this->figure('fs43-filter.png', 'Alur kiri ke kanan: browser GET device_id, Flask, SQLite, saring meja-02, JSON jumlah 5', '<strong>Gambar utama — GET <code>?device_id=</code> memisahkan dua stasiun.</strong> Baca dari kiri ke kanan: browser → Flask → SQLite → JSON meja-02 jumlah 5. Diagram buatan Koding Indonesia (FS-43).');
        $browser = $this->figure('fs43-browser-json.png', 'Ilustrasi browser menampilkan JSON jumlah 5 untuk device_id esp32-meja-02', '<strong>Browser sudah menampilkan JSON tersaring.</strong> Yang dikunci adalah <code>"jumlah": 5</code> dan <code>"device_id": "esp32-meja-02"</code>. Ilustrasi buatan Koding Indonesia (FS-43), meniru jendela browser. Tampilan resmi tidak dipakai utuh.');
        $command = $this->figure('fs43-command-topic.png', 'Alur kiri ke kanan: uji_perintah.py, Flask POST, topic meja-02 command, MQTTX', '<strong>POST perintah memakai nama yang sama di topic.</strong> Baca dari kiri ke kanan: script → Flask → <code>.../esp32-meja-02/command</code> → MQTTX. Diagram buatan Koding Indonesia (FS-43).');
        $troubleshooting = $this->figure('fs43-troubleshooting.png', 'Empat pemeriksaan: Flask, SQLite isi_dua, MQTTX dua topic, Username vs device_id', '<strong>Skema bantu.</strong> Flask ke <code>127.0.0.1:5000</code>. Jangan menukar Username MQTTX dengan <code>device_id</code>. Diagram buatan Koding Indonesia (FS-43).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan ketik perintah Python dulu.'],
            ['title' => 'Buka MQTTX', 'text' => 'Connect ke <code>127.0.0.1:1883</code>. Subscribe dua topic telemetry: <code>.../esp32-meja-01/telemetry</code> dan <code>.../esp32-meja-02/telemetry</code>. Jangan Publish dulu.'],
            ['title' => 'Buka Notepad, tulis berkas', 'text' => 'Simpan <code>isi_dua_stasiun.py</code> dan perbarui <code>pintu_stasiun.py</code> di folder <code>Documents\\fsiot-fs39</code>. All files, bukan <code>.txt</code>.'],
            ['title' => 'Buka PowerShell, isi lalu Flask', 'text' => 'Start → ketik PowerShell. Tidak perlu <em>Run as administrator</em>. Jalankan <code>isi_dua_stasiun.py</code>, lalu Flask. Jendela Flask tetap terbuka.'],
            ['title' => 'Buka browser, bandingkan dua GET', 'text' => 'Tab baru: <code>http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02</code> harus menampilkan <code>"jumlah": 5</code>. Tab lain: saring meja-01, tanpa baris meja-02.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = <code>isi_dua_stasiun.py</code> menulis <code>5 baris meja-02 siap.</code> dan browser GET meja-02 menampilkan <code>"jumlah": 5</code>. ESP32 boleh menyala, tetapi tidak wajib.');

        return <<<'HTML'
<h2>Pendahuluan — dua stasiun, dua nama</h2>
<p><strong>FS-43 / #113 (ini)</strong> adalah lab identitas. Kemarin Flask sudah membuka pintu REST. Hari ini tugasnya lain: <strong>beri nama dua stasiun, lalu saring datanya</strong> supaya baris meja-01 tidak tertukar dengan meja-02.</p>
<p><strong>Intinya:</strong> isi 5 baris <code>esp32-meja-02</code> ke <code>stasiun.db</code>, perbarui Flask supaya GET memahami <code>?device_id=</code>, buka dua alamat di browser, lalu kirim perintah ke topic yang sama dengan namanya.</p>
<p><strong>Analogi:</strong> dua kotak di satu gudang. Tanpa label, semua barang tercampur. Dengan label, petugas pintu (Flask) bisa mengambil hanya kotak meja-02. Dashboard cantik belum dibangun — itu FS-44.</p>
<p>Prasyarat lab: FS-42 (Flask sudah pernah terbuka), FS-40 (<code>stasiun.db</code>), FS-34 (pola topic). FS-41 MariaDB <strong>tidak wajib</strong>. ESP32 <strong>boleh menyala</strong>, dan <strong>boleh dicabut</strong> — satu papan cukup, bahkan tanpa papan pun lab ini jalan. Tidak ada kabel baru, tidak ada Upload, <strong>Bukan AC 220V</strong>.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>MQTTX Connected ke <code>127.0.0.1:1883</code> dan sudah Subscribe dua topic telemetry.</li>
<li><code>isi_dua_stasiun.py</code> mencetak <code>5 baris meja-02 siap.</code></li>
<li>Berkas <code>stasiun.db</code> masih ada; baris meja-01 dari FS-40 tidak dihapus.</li>
<li>PowerShell menampilkan <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>Browser di <code>http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02</code> menampilkan <code>"jumlah": 5</code>.</li>
<li>GET meja-01 hanya berisi <code>esp32-meja-01</code>.</li>
<li><code>uji_perintah.py</code> mencetak <code>Perintah terkirim.</code> ke topic meja-02.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> belum dashboard HTML, belum CORS, belum MySQL, belum sandi Mosquitto sebagai syarat lulus. Bukti cukup = JSON tersaring + perintah jatuh ke nama yang sama. <code>flask==3.1.3</code> sudah dari FS-42.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>device_id</strong> — nama stasiun. Hari ini ada dua: <code>esp32-meja-01</code> dan <code>esp32-meja-02</code>.</li>
<li><strong>Topic</strong> — alamat MQTT. Nama stasiun ada di tengah, misalnya <code>.../esp32-meja-02/telemetry</code>.</li>
<li><strong>Saring</strong> — GET dengan <code>?device_id=</code> supaya JSON hanya milik satu nama.</li>
<li><strong>Username MQTTX</strong> — kolom login broker. <strong>Bukan</strong> <code>device_id</code>. Hari ini biarkan kosong.</li>
<li><strong>GET /telemetry</strong> — pintu baca. Tanpa saringan masih boleh; dengan saringan, itulah bukti hari ini.</li>
<li><strong>POST /command</strong> — pintu perintah. Topic command harus memakai <code>device_id</code> yang sama.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC 220V, <code>file://</code>, membuka port 5000 ke internet, mengubah ExecutionPolicy, atau menyalakan sandi Mosquitto di <code>C:\Program Files\mosquitto\</code>. Thunder Client tidak wajib. Node-RED boleh tetap terbuka.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa device_id, bukan satu nama untuk semua</h2>
HTML
            .$why.<<<'HTML'
<p>Kalau dua papan — atau dua nama di JSON — memakai <code>esp32-meja-01</code> semua, gudang tidak bisa membedakan. Filter API lalu menampilkan campur. Kesalahan awam yang paling sering: menyalin sketch yang sama ke papan kedua tanpa ganti nama.</p>
<p>Hari ini kita tidak wajib punya papan kedua. Cukup nama kedua di SQLite dan di topic.</p>

<h2>Baca topic kiri ke kanan</h2>
HTML
            .$topic.<<<'HTML'
<p>Pola yang dikunci sejak FS-34:</p>
<pre><code>kodingindonesia/fsiot/{device_id}/telemetry
kodingindonesia/fsiot/{device_id}/command</code></pre>
<p>Ganti <code>{device_id}</code> menjadi <code>esp32-meja-01</code> atau <code>esp32-meja-02</code>. Jangan ganti Username di MQTTX. Jangan menukar urutan segmen.</p>

<h2>Satu papan cukup, ganti nama</h2>
HTML
            .$names.<<<'HTML'
<p>ESP32 <strong>boleh dicabut</strong>. Kalau papan masih di meja, jangan Upload sketch baru hari ini. Nanti di lapangan, papan kedua mendapat nama sendiri — bukan menyalin nama pertama.</p>

<h2>Nyalakan MQTTX, langganan dua topic</h2>
HTML
            .$mqttx.<<<'HTML'
<p><strong>Buka dulu MQTTX.</strong> Host <code>127.0.0.1</code>, port <code>1883</code>, tekan Connect. Username dan Password biarkan kosong. Lalu Subscribe, satu per satu:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry
kodingindonesia/fsiot/esp32-meja-02/telemetry</code></pre>
<p><strong>Hasil yang dicari:</strong> status Connected, dua langganan terlihat, daftar pesan boleh masih kosong. Jangan tekan Publish.</p>
<p>Kalau Connect gagal: nyalakan Mosquitto seperti di FS-33. Python ke broker yang sama, <code>127.0.0.1</code>, bukan IPv4 ESP32.</p>

<h2>Tulis isi_dua_stasiun.py</h2>
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>. Folder <code>.venv</code>, berkas <code>stasiun.db</code>, dan <code>pintu_stasiun.py</code> dari FS-42 harus sudah ada. Jika <code>stasiun.db</code> belum ada, ulangi FS-40 dulu.</p>
<p><strong>Buka dulu Notepad.</strong> Tempel kode ini. File → Save As, All files, nama <code>isi_dua_stasiun.py</code>, folder lab. Script ini <strong>tidak menghapus</strong> baris meja-01. Ia hanya menyiapkan ulang 5 baris meja-02.</p>
<pre><code class="language-python">
HTML
            .$isi.<<<'HTML'
</code></pre>
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe isi_dua_stasiun.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>5 baris meja-02 siap.</code> Angka meja-01 boleh 10 atau lebih. Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>.</p>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python isi_dua_stasiun.py</code>.</p>

<h2>Perbarui pintu_stasiun.py supaya bisa saring</h2>
HTML
            .$filter.<<<'HTML'
<p><code>requirements.txt</code> tetap mengunci <code>flask==3.1.3</code> dan <code>paho-mqtt==2.1.0</code> seperti FS-42. Jangan pip ke Python global. Kalau Flask sudah terpasang, tidak perlu pip ulang.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Buka dulu Notepad.</strong> Ganti isi <code>pintu_stasiun.py</code> dengan kode di bawah. Save As, All files, folder <code>Documents\fsiot-fs39</code>.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>GET tanpa <code>?device_id=</code> masih mengembalikan campuran (paling banyak 10 baris terakhir). GET dengan saringan hanya satu nama. POST memakai <code>topic_command(device_id)</code> supaya perintah meja-02 tidak jatuh ke topic meja-01.</p>
<p>Jika Flask hilang dari venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Buka dua alamat GET di browser</h2>
HTML
            .$browser.<<<'HTML'
<p>Tutup Flask lama jika masih jalan, lalu <strong>Buka dulu PowerShell</strong> di folder lab:</p>
<pre><code>.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>. Jendela ini <strong>tetap terbuka</strong>.</p>
<p><strong>Buka browser</strong> di tab baru. Ketik alamat ini, lalu Enter:</p>
<pre><code>http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>"jumlah": 5</code> dan <code>"device_id": "esp32-meja-02"</code>. Semua <code>baris</code> memakai nama itu. Angka suhu boleh berbeda. Ini JSON, bukan halaman ber tombol. <strong>Jangan buka berkas HTML lewat <code>file://</code>.</strong></p>
<p>Tab lain, bandingkan:</p>
<pre><code>http://127.0.0.1:5000/telemetry?device_id=esp32-meja-01</code></pre>
<p>Di sini <code>jumlah</code> boleh lebih dari 5. Yang dikunci: tidak ada <code>esp32-meja-02</code> di dalam daftar.</p>

<h2>Kirim perintah ke nama yang tepat</h2>
HTML
            .$command.<<<'HTML'
<p>Masih di MQTTX, Subscribe juga:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-02/command</code></pre>
<p>Biarkan Flask berjalan. <strong>Buka dulu Notepad</strong>, simpan <code>uji_perintah.py</code> (boleh menimpa berkas FS-42):</p>
<pre><code class="language-python">
HTML
            .$uji.<<<'HTML'
</code></pre>
<p><strong>Buka dulu PowerShell</strong> jendela kedua:</p>
<pre><code>.\.venv\Scripts\python.exe uji_perintah.py</code></pre>
<p><strong>Hasil yang dicari:</strong> script mencetak JSON <code>"ok": true</code> lalu <code>Perintah terkirim.</code> MQTTX menampilkan pesan di topic meja-02, bukan meja-01.</p>
<p>Kalau ESP32 masih menjalankan firmware perintah, relay ikut hanya jika nama di sketch sama. Kalau papan dicabut, MQTTX tetap cukup. Terminal NC/COM/NO tetap kosong. <strong>Bukan AC 220V.</strong></p>

<h2>Nama stasiun bukan Username MQTTX</h2>
<p>Kolom Username dan Password di MQTTX adalah login ke broker. <code>device_id</code> adalah nama di JSON dan topic. Mencampur keduanya membuat filter API tampak “aneh” padahal broker saja yang ditolak.</p>
<p>Kurikulum menyebut user/password Mosquitto. Di lab Windows, mengubah <code>mosquitto.conf</code> di <code>C:\Program Files\mosquitto\</code> butuh Administrator dan bisa memutus FS-33 sampai FS-42 jika <code>allow_anonymous false</code> dinyalakan. <strong>Jangan diubah hari ini.</strong> Identitas yang dikunci adalah <code>device_id</code>.</p>
<p>Tidak wajib. Kalau JSON tersaring dan perintah jatuh ke nama yang benar, lab utama selesai.</p>

<h2>Bonus: kirim dua nama ke MQTTX</h2>
<p>Tidak wajib. Setelah dua langganan telemetry terlihat, <strong>Buka dulu Notepad</strong>, simpan <code>kirim_dua_stasiun.py</code>:</p>
<pre><code class="language-python">
HTML
            .$kirim.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe kirim_dua_stasiun.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>Dua nama terkirim ke MQTTX.</code> Dua topic telemetry masing-masing dapat satu JSON. Ini tidak mengganti bukti GET saringan. Dashboard HTML ditunda ke FS-44. <strong>Bukan AC 220V.</strong></p>

<h2>Jika data masih campur</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Flask belum terbuka.</strong> Jendela harus menampilkan <code>Pintu stasiun terbuka</code>. Jangan ditutup sebelum dua GET selesai.</li>
<li><strong>isi_dua belum jalan.</strong> Ulangi sampai <code>5 baris meja-02 siap.</code> GET meja-02 tidak akan bernilai 5 jika baris itu belum ada.</li>
<li><strong>MQTTX baru satu topic.</strong> Subscribe nama kedua. Jangan Publish dulu.</li>
<li><strong>Username MQTTX diisi device_id.</strong> Kosongkan Username. Nama stasiun hanya di JSON dan topic.</li>
<li><strong>pintu_stasiun.py masih versi FS-42.</strong> Versi lama tidak membaca <code>?device_id=</code> dan mengunci topic command ke meja-01.</li>
</ol>

<h2 id="fsiot-device-checklist">Checklist sebelum FS-44</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-device-checklist-items">
<li>MQTTX Connected ke 127.0.0.1:1883 dan Subscribe dua topic telemetry.</li>
<li><code>isi_dua_stasiun.py</code> mencetak 5 baris meja-02 siap.</li>
<li>Berkas <code>stasiun.db</code> masih ada di folder lab.</li>
<li>PowerShell menampilkan Pintu stasiun terbuka di port 5000.</li>
<li>Browser GET <code>?device_id=esp32-meja-02</code> menampilkan <code>"jumlah": 5</code>.</li>
<li>GET meja-01 tidak menampilkan baris meja-02.</li>
<li><code>uji_perintah.py</code> mencetak Perintah terkirim ke topic meja-02.</li>
<li>Saya tidak menukar device_id dengan Username MQTTX.</li>
<li>Saya tidak mengubah ExecutionPolicy.</li>
<li>Saya tidak memakai MySQL atau dashboard HTML hari ini.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: dua nama → isi 5 baris meja-02 → Flask saring → JSON 5 → perintah ke topic yang sama. Pada FS-44, JSON ini mulai tampil di halaman HTML. MariaDB tetap opsional.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Hardcode nama yang sama di semua papan.</strong> Papan kedua wajib nama kedua.</li>
<li><strong>Mengisi Username MQTTX dengan device_id.</strong> Itu login broker, bukan nama stasiun.</li>
<li><strong>Menyalakan sandi Mosquitto tanpa rencana balik.</strong> Lab sebelumnya bisa putus.</li>
<li><strong>Menutup jendela Flask sebelum GET.</strong> Pintu harus tetap terbuka.</li>
<li><strong>Mengubah ExecutionPolicy.</strong> Tetap pakai <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Membuka file://.</strong> JSON hanya muncul jika Flask yang menyajikan <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Membangun dashboard HTML hari ini.</strong> Ditunda ke FS-44.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Harus beli ESP32 kedua?</h3>
<p>Tidak. Satu papan cukup, atau tanpa papan. Nama kedua hidup di SQLite dan topic.</p>
<h3>Kenapa bukan Username MQTTX?</h3>
<p>Username adalah kunci pintu broker. <code>device_id</code> adalah label barang di dalam gudang. Keduanya beda pekerjaan.</p>
<h3>Wajib sandi Mosquitto hari ini?</h3>
<p>Tidak. Konsepnya: broker <em>bisa</em> memakai user/password. Mengubah file di Program Files butuh Administrator dan memutus lab lama. Ditunda.</p>
<h3>Boleh Subscribe pakai tanda + ?</h3>
<p>Nanti. Hari ini Subscribe dua topic penuh, supaya kelihatan dua nama. Wildcard mudah tertukar dengan “semua tercampur”.</p>
<h3>Apakah dashboard HTML hari ini?</h3>
<p>Tidak. JSON tersaring adalah bukti. Halaman angka ada di FS-44.</p>
<h3>ESP32 wajib menyala?</h3>
<p>Tidak. MQTTX dan browser cukup. <strong>Bukan AC 220V.</strong></p>
<h3>Apakah stasiun.db dihapus?</h3>
<p>Tidak. Script isi hanya mengulang 5 baris meja-02. Baris meja-01 tetap.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://flask.palletsprojects.com/en/stable/api/#flask.Request.args" target="_blank" rel="noopener noreferrer">Flask Request.args</a> — cara membaca <code>?device_id=</code> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">mosquitto.conf</a> — opsi <code>password_file</code> (dibaca sebagai konsep, tidak diubah hari ini)</li>
<li><a href="https://commons.wikimedia.org/wiki/File:ESP32_Espressif_ESP-WROOM-32_Dev_Board.jpg" target="_blank" rel="noopener noreferrer">ESP32 Espressif ESP-WROOM-32 Dev Board</a> — foto oleh Ubahnverleih, Wikimedia Commons, <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="noopener noreferrer">CC0 1.0</a>. Dipakai sebagai rujukan rupa papan saja; tidak disematkan di artikel karena label GPIO mudah disalin salah.</li>
<li>Diagram urutan tools, campur versus terpisah, topic, satu papan dua nama, saringan GET, alur perintah, dan skema periksa — Koding Indonesia (FS-43).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> dua stasiun sudah punya nama. Browser menyaring JSON meja-02 menjadi 5 baris, perintah jatuh ke topic yang sama. Pada <strong>FS-44</strong>, JSON ini mulai tampil di halaman HTML. Jangan <code>file://</code>. MariaDB tetap opsional.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $isi = htmlspecialchars($this->isi(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $uji = htmlspecialchars($this->uji(), ENT_QUOTES, 'UTF-8');
        $kirim = htmlspecialchars($this->kirim(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs43-tools-order.png', 'Five-step order: browser, MQTTX two telemetry topics, Notepad, PowerShell, browser GET filter', '<strong>Desk order (five steps):</strong> browser → MQTTX Connect 127.0.0.1:1883 then Subscribe two telemetry topics → Notepad writes the files → PowerShell fills SQLite then Flask → browser GET <code>?device_id=</code>. Diagram by Koding Indonesia (FS-43).');
        $why = $this->figure('fs43-why-id.png', 'Comparison of mixed unnamed data versus two named stations that can be filtered', '<strong>Without names, data piles up. With names, you can filter.</strong> Today’s identity is <code>device_id</code>, not the MQTTX Username. Diagram by Koding Indonesia (FS-43).');
        $topic = $this->figure('fs43-topic.png', 'Left-to-right flow: kodingindonesia, fsiot, device_id, telemetry or command', '<strong>Read the topic left to right.</strong> The station name sits in the middle: <code>esp32-meja-01</code> or <code>esp32-meja-02</code>. Diagram by Koding Indonesia (FS-43).');
        $mqttx = $this->figure('fs43-mqttx.png', 'MQTTX illustration Connected to 127.0.0.1:1883 with two telemetry topic subscriptions', '<strong>MQTTX is already subscribed to two names.</strong> Connect first, then Subscribe twice. Do not Publish yet. Illustration by Koding Indonesia (FS-43), modelled on <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0). The official window screenshot is not used as-is.');
        $names = $this->figure('fs43-two-names.png', 'One ESP32 board with two station names esp32-meja-01 and esp32-meja-02', '<strong>One board is enough.</strong> What you change is the name in JSON and the topic, not pin order. Diagram by Koding Indonesia (FS-43). A board photo is cited in Sources (Wikimedia Commons, CC0) — the GPIO photo is not embedded here so pin labels are not copied by mistake.');
        $filter = $this->figure('fs43-filter.png', 'Left-to-right flow: browser GET device_id, Flask, SQLite, filter meja-02, JSON count 5', '<strong>Main figure — GET <code>?device_id=</code> separates two stations.</strong> Read left to right: browser → Flask → SQLite → JSON meja-02 count 5. Diagram by Koding Indonesia (FS-43).');
        $browser = $this->figure('fs43-browser-json.png', 'Browser illustration showing JSON jumlah 5 for device_id esp32-meja-02', '<strong>The browser is already showing filtered JSON.</strong> The lock is <code>"jumlah": 5</code> and <code>"device_id": "esp32-meja-02"</code>. Illustration by Koding Indonesia (FS-43), modelled on a browser window. The official window is not used as-is.');
        $command = $this->figure('fs43-command-topic.png', 'Left-to-right flow: uji_perintah.py, Flask POST, meja-02 command topic, MQTTX', '<strong>POST the command using the same name in the topic.</strong> Read left to right: script → Flask → <code>.../esp32-meja-02/command</code> → MQTTX. Diagram by Koding Indonesia (FS-43).');
        $troubleshooting = $this->figure('fs43-troubleshooting.png', 'Four checks: Flask, SQLite isi_dua, MQTTX two topics, Username vs device_id', '<strong>Helper schematic.</strong> Flask uses <code>127.0.0.1:5000</code>. Do not swap the MQTTX Username with <code>device_id</code>. Diagram by Koding Indonesia (FS-43).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not type Python commands yet.'],
            ['title' => 'Open MQTTX', 'text' => 'Connect to <code>127.0.0.1:1883</code>. Subscribe to two telemetry topics: <code>.../esp32-meja-01/telemetry</code> and <code>.../esp32-meja-02/telemetry</code>. Do not Publish yet.'],
            ['title' => 'Open Notepad, write the files', 'text' => 'Save <code>isi_dua_stasiun.py</code> and update <code>pintu_stasiun.py</code> in <code>Documents\\fsiot-fs39</code>. All files, not <code>.txt</code>.'],
            ['title' => 'Open PowerShell, fill then Flask', 'text' => 'Start → type PowerShell. You do not need <em>Run as administrator</em>. Run <code>isi_dua_stasiun.py</code>, then Flask. Leave the Flask window open.'],
            ['title' => 'Open a browser, compare two GETs', 'text' => 'New tab: <code>http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02</code> must show <code>"jumlah": 5</code>. Other tab: filter meja-01, with no meja-02 rows.'],
        ], '<strong>How to test today:</strong> success = <code>isi_dua_stasiun.py</code> prints <code>5 baris meja-02 siap.</code> and the browser GET for meja-02 shows <code>"jumlah": 5</code>. The ESP32 may be on, but it is not required.');

        return <<<'HTML'
<h2>Introduction — two stations, two names</h2>
<p><strong>FS-43 / #113 (this article)</strong> is the identity lab. Yesterday Flask already opened the REST door. Today the job is different: <strong>name two stations, then filter their data</strong> so meja-01 rows are not mixed with meja-02.</p>
<p><strong>In short:</strong> add 5 <code>esp32-meja-02</code> rows to <code>stasiun.db</code>, update Flask so GET understands <code>?device_id=</code>, open two browser addresses, then send a command to the topic that matches the name.</p>
<p><strong>Analogy:</strong> two boxes in one store. Without labels, everything mixes. With labels, the door clerk (Flask) can fetch only the meja-02 box. A pretty dashboard is not built yet — that is FS-44.</p>
<p>Lab prerequisites: FS-42 (Flask has opened before), FS-40 (<code>stasiun.db</code>), FS-34 (topic pattern). FS-41 MariaDB is <strong>not required</strong>. The ESP32 <strong>may stay on</strong>, and <strong>may be unplugged</strong> — one board is enough, and the lab still works with no board at all. No new cables, no Upload, <strong>Not AC mains</strong>.</p>

<h2>Expected outcome</h2>
<ul>
<li>MQTTX is Connected to <code>127.0.0.1:1883</code> and has Subscribed to two telemetry topics.</li>
<li><code>isi_dua_stasiun.py</code> prints <code>5 baris meja-02 siap.</code></li>
<li>The file <code>stasiun.db</code> is still there; meja-01 rows from FS-40 are not deleted.</li>
<li>PowerShell shows <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>The browser at <code>http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02</code> shows <code>"jumlah": 5</code>.</li>
<li>GET for meja-01 contains only <code>esp32-meja-01</code>.</li>
<li><code>uji_perintah.py</code> prints <code>Perintah terkirim.</code> to the meja-02 topic.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> no HTML dashboard, no CORS, no MySQL, no Mosquitto password as a pass gate. Enough proof = filtered JSON + a command on the matching name. <code>flask==3.1.3</code> is already from FS-42.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>device_id</strong> — the station name. Today there are two: <code>esp32-meja-01</code> and <code>esp32-meja-02</code>.</li>
<li><strong>Topic</strong> — the MQTT address. The station name sits in the middle, for example <code>.../esp32-meja-02/telemetry</code>.</li>
<li><strong>Filter</strong> — GET with <code>?device_id=</code> so the JSON belongs to one name only.</li>
<li><strong>MQTTX Username</strong> — the broker login field. It is <strong>not</strong> <code>device_id</code>. Leave it empty today.</li>
<li><strong>GET /telemetry</strong> — the read door. Unfiltered GET is still allowed; filtered GET is today’s proof.</li>
<li><strong>POST /command</strong> — the command door. The command topic must use the same <code>device_id</code>.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC mains, <code>file://</code>, opening port 5000 to the internet, changing ExecutionPolicy, or turning on a Mosquitto password in <code>C:\Program Files\mosquitto\</code>. Thunder Client is not required. Node-RED may stay open.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Why device_id, not one name for everyone</h2>
HTML
            .$why.<<<'HTML'
<p>If two boards — or two names in JSON — all use <code>esp32-meja-01</code>, the store cannot tell them apart. The API filter then shows a mix. The most common beginner mistake: copying the same sketch onto a second board without changing the name.</p>
<p>Today you do not need a second board. A second name in SQLite and in the topic is enough.</p>

<h2>Read the topic left to right</h2>
HTML
            .$topic.<<<'HTML'
<p>The pattern locked since FS-34:</p>
<pre><code>kodingindonesia/fsiot/{device_id}/telemetry
kodingindonesia/fsiot/{device_id}/command</code></pre>
<p>Replace <code>{device_id}</code> with <code>esp32-meja-01</code> or <code>esp32-meja-02</code>. Do not change the Username in MQTTX. Do not swap the segment order.</p>

<h2>One board is enough, change the name</h2>
HTML
            .$names.<<<'HTML'
<p>The ESP32 <strong>may be unplugged</strong>. If the board is still on the desk, do not Upload a new sketch today. In the field later, the second board gets its own name — it does not copy the first name.</p>

<h2>Start MQTTX, subscribe to two topics</h2>
HTML
            .$mqttx.<<<'HTML'
<p><strong>Open MQTTX first.</strong> Host <code>127.0.0.1</code>, port <code>1883</code>, press Connect. Leave Username and Password empty. Then Subscribe, one at a time:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry
kodingindonesia/fsiot/esp32-meja-02/telemetry</code></pre>
<p><strong>What you want:</strong> status Connected, two subscriptions visible, the message list may still be empty. Do not press Publish.</p>
<p>If Connect fails: start Mosquitto as in FS-33. Python uses the same broker, <code>127.0.0.1</code>, not the ESP32 IPv4.</p>

<h2>Write isi_dua_stasiun.py</h2>
<p><strong>Open File Explorer first</strong>, go to <code>Documents\fsiot-fs39</code>. The <code>.venv</code> folder, the <code>stasiun.db</code> file, and <code>pintu_stasiun.py</code> from FS-42 must already be there. If <code>stasiun.db</code> is missing, repeat FS-40 first.</p>
<p><strong>Open Notepad first.</strong> Paste this code. File → Save As, All files, name <code>isi_dua_stasiun.py</code>, lab folder. This script <strong>does not delete</strong> meja-01 rows. It only prepares 5 meja-02 rows again.</p>
<pre><code class="language-python">
HTML
            .$isi.<<<'HTML'
</code></pre>
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste a command:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe isi_dua_stasiun.py</code></pre>
<p><strong>What you want:</strong> <code>5 baris meja-02 siap.</code> The meja-01 count may be 10 or more. If <code>.\.venv\Scripts\Activate.ps1</code> is rejected, <strong>do not change ExecutionPolicy</strong>.</p>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python isi_dua_stasiun.py</code>.</p>

<h2>Update pintu_stasiun.py so it can filter</h2>
HTML
            .$filter.<<<'HTML'
<p><code>requirements.txt</code> still pins <code>flask==3.1.3</code> and <code>paho-mqtt==2.1.0</code> as in FS-42. Do not pip into global Python. If Flask is already installed, you do not need to pip again.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Open Notepad first.</strong> Replace <code>pintu_stasiun.py</code> with the code below. Save As, All files, folder <code>Documents\fsiot-fs39</code>.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>GET without <code>?device_id=</code> still returns a mix (at most the last 10 rows). GET with a filter returns one name. POST uses <code>topic_command(device_id)</code> so a meja-02 command does not land on the meja-01 topic.</p>
<p>If Flask is missing from the venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Open two GET addresses in the browser</h2>
HTML
            .$browser.<<<'HTML'
<p>Close the old Flask window if it is still running, then <strong>Open PowerShell first</strong> in the lab folder:</p>
<pre><code>.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>What you want:</strong> <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>. Leave this window <strong>open</strong>.</p>
<p><strong>Open a browser</strong> in a new tab. Type this address, then Enter:</p>
<pre><code>http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02</code></pre>
<p><strong>What you want:</strong> <code>"jumlah": 5</code> and <code>"device_id": "esp32-meja-02"</code>. Every row uses that name. Temperature numbers may differ. This is JSON, not a page with buttons. <strong>Do not open an HTML file through <code>file://</code>.</strong></p>
<p>In another tab, compare:</p>
<pre><code>http://127.0.0.1:5000/telemetry?device_id=esp32-meja-01</code></pre>
<p>Here <code>jumlah</code> may be more than 5. The lock: there is no <code>esp32-meja-02</code> in the list.</p>

<h2>Send the command to the matching name</h2>
HTML
            .$command.<<<'HTML'
<p>Still in MQTTX, also Subscribe:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-02/command</code></pre>
<p>Leave Flask running. <strong>Open Notepad first</strong>, save <code>uji_perintah.py</code> (you may overwrite the FS-42 file):</p>
<pre><code class="language-python">
HTML
            .$uji.<<<'HTML'
</code></pre>
<p><strong>Open PowerShell first</strong> in a second window:</p>
<pre><code>.\.venv\Scripts\python.exe uji_perintah.py</code></pre>
<p><strong>What you want:</strong> the script prints JSON <code>"ok": true</code> then <code>Perintah terkirim.</code> MQTTX shows the message on the meja-02 topic, not meja-01.</p>
<p>If the ESP32 still runs command firmware, the relay follows only when the sketch name matches. If the board is unplugged, MQTTX is still enough. The NC/COM/NO terminals stay empty. <strong>Not AC mains.</strong></p>

<h2>A station name is not the MQTTX Username</h2>
<p>The Username and Password fields in MQTTX are the broker login. <code>device_id</code> is the name in JSON and the topic. Mixing them makes the API filter look “odd” when it is only the broker rejecting the connection.</p>
<p>The curriculum mentions a Mosquitto user/password. On a Windows lab, editing <code>mosquitto.conf</code> under <code>C:\Program Files\mosquitto\</code> needs Administrator rights and can break FS-33 through FS-42 if <code>allow_anonymous false</code> is turned on. <strong>Do not change it today.</strong> The locked identity is <code>device_id</code>.</p>
<p>Not required. If the JSON is filtered and the command lands on the right name, the main lab is done.</p>

<h2>Bonus: send two names to MQTTX</h2>
<p>Not required. After the two telemetry subscriptions are visible, <strong>Open Notepad first</strong>, save <code>kirim_dua_stasiun.py</code>:</p>
<pre><code class="language-python">
HTML
            .$kirim.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe kirim_dua_stasiun.py</code></pre>
<p><strong>What you want:</strong> <code>Dua nama terkirim ke MQTTX.</code> Each telemetry topic gets one JSON message. This does not replace the filtered GET proof. The HTML dashboard waits for FS-44. <strong>Not AC mains.</strong></p>

<h2>If the data is still mixed</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Flask is not open.</strong> The window must show <code>Pintu stasiun terbuka</code>. Do not close it before both GETs are done.</li>
<li><strong>isi_dua has not run.</strong> Repeat until <code>5 baris meja-02 siap.</code> GET meja-02 will not be 5 if those rows are missing.</li>
<li><strong>MQTTX has only one topic.</strong> Subscribe the second name. Do not Publish yet.</li>
<li><strong>The MQTTX Username is filled with device_id.</strong> Clear Username. The station name lives only in JSON and the topic.</li>
<li><strong>pintu_stasiun.py is still the FS-42 version.</strong> The old file does not read <code>?device_id=</code> and locks the command topic to meja-01.</li>
</ol>

<h2 id="fsiot-device-checklist">Checklist before FS-44</h2>
<p>Tick after you have actually done each item. Target: <strong>10/10</strong>. Progress stays in this browser on your device and is not sent to the server.</p>
<ul id="fsiot-device-checklist-items">
<li>MQTTX is Connected to 127.0.0.1:1883 and Subscribed to two telemetry topics.</li>
<li><code>isi_dua_stasiun.py</code> prints 5 baris meja-02 siap.</li>
<li>The file <code>stasiun.db</code> is still in the lab folder.</li>
<li>PowerShell shows Pintu stasiun terbuka on port 5000.</li>
<li>Browser GET <code>?device_id=esp32-meja-02</code> shows <code>"jumlah": 5</code>.</li>
<li>GET meja-01 does not show meja-02 rows.</li>
<li><code>uji_perintah.py</code> prints Perintah terkirim to the meja-02 topic.</li>
<li>I did not swap device_id with the MQTTX Username.</li>
<li>I did not change ExecutionPolicy.</li>
<li>I did not use MySQL or an HTML dashboard today.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: two names → fill 5 meja-02 rows → Flask filters → JSON 5 → command on the matching topic. In FS-44, this JSON starts showing on an HTML page. MariaDB stays optional.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Hard-coding the same name on every board.</strong> The second board needs the second name.</li>
<li><strong>Filling the MQTTX Username with device_id.</strong> That is the broker login, not the station name.</li>
<li><strong>Turning on a Mosquitto password with no way back.</strong> Earlier labs can break.</li>
<li><strong>Closing the Flask window before GET.</strong> The door must stay open.</li>
<li><strong>Changing ExecutionPolicy.</strong> Keep using <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Opening file://.</strong> JSON only appears when Flask serves <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Building an HTML dashboard today.</strong> That waits for FS-44.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Do I have to buy a second ESP32?</h3>
<p>No. One board is enough, or no board at all. The second name lives in SQLite and the topic.</p>
<h3>Why not the MQTTX Username?</h3>
<p>Username is the broker door key. <code>device_id</code> is the label on goods inside the store. They do different jobs.</p>
<h3>Is a Mosquitto password required today?</h3>
<p>No. The idea: a broker <em>can</em> use a user/password. Editing files under Program Files needs Administrator rights and breaks older labs. It waits.</p>
<h3>May I Subscribe with a + wildcard?</h3>
<p>Later. Today Subscribe two full topics so the two names stay visible. A wildcard is easy to mix up with “everything is mixed”.</p>
<h3>Is the HTML dashboard today?</h3>
<p>No. Filtered JSON is the proof. The numbers page is FS-44.</p>
<h3>Must the ESP32 stay on?</h3>
<p>No. MQTTX and the browser are enough. <strong>Not AC mains.</strong></p>
<h3>Is stasiun.db deleted?</h3>
<p>No. The fill script only repeats 5 meja-02 rows. meja-01 rows stay.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://flask.palletsprojects.com/en/stable/api/#flask.Request.args" target="_blank" rel="noopener noreferrer">Flask Request.args</a> — how to read <code>?device_id=</code> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li><a href="https://mosquitto.org/man/mosquitto-conf-5.html" target="_blank" rel="noopener noreferrer">mosquitto.conf</a> — the <code>password_file</code> option (read as a concept, not changed today)</li>
<li><a href="https://commons.wikimedia.org/wiki/File:ESP32_Espressif_ESP-WROOM-32_Dev_Board.jpg" target="_blank" rel="noopener noreferrer">ESP32 Espressif ESP-WROOM-32 Dev Board</a> — photo by Ubahnverleih, Wikimedia Commons, <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="noopener noreferrer">CC0 1.0</a>. Used as a board-appearance reference only; not embedded in the article because GPIO labels are easy to copy by mistake.</li>
<li>Diagrams for tool order, mixed versus separate, topic, one board two names, GET filter, command flow, and the check schematic — Koding Indonesia (FS-43).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> two stations now have names. The browser filters meja-02 JSON to 5 rows, and the command lands on the matching topic. In <strong>FS-44</strong>, this JSON starts showing on an HTML page. Do not use <code>file://</code>. MariaDB stays optional.</p>
HTML;
    }
}
