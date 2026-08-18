<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article110Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-python-mqtt-sqlite-stasiun';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'python', 'mqtt', 'sqlite', 'esp32'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Terima data stasiun di Python lalu simpan ke SQLite',
            'title_en' => 'Receive station data in Python then store it in SQLite',
            'excerpt' => 'FS-40 / #110: paho-mqtt di venv FS-39 berlangganan telemetry, mencetak pesan, menyimpan CSV lalu 10 baris SQLite. Node-RED tetap otak visual.',
            'excerpt_en' => 'FS-40 / #110: paho-mqtt in the FS-39 venv subscribes to telemetry, prints messages, then stores CSV and 10 SQLite rows. Node-RED stays the visual brain.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Python Terima MQTT lalu Simpan ke SQLite — FS-40',
            'seo_title_en' => 'Python MQTT into SQLite for the Station — FS-40',
            'seo_description' => 'Lab pemula: pasang paho-mqtt 2.1.0 di venv, berlangganan Mosquitto lokal, simpan CSV dan 10 baris SQLite. Belum Flask, belum MySQL.',
            'seo_description_en' => 'A first lab: install paho-mqtt 2.1.0 in the venv, subscribe to local Mosquitto, store CSV and 10 SQLite rows. No Flask, no MySQL yet.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'mqtt', 'sqlite', 'esp32'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs40-cover-sqlite.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs40-cover-sqlite.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs40-cover-sqlite.webp',
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
        return 'paho-mqtt==2.1.0';
    }

    private function terima(): string
    {
        return implode("\n", [
            'import csv',
            'import json',
            'import sqlite3',
            'from datetime import datetime, timezone',
            'from pathlib import Path',
            '',
            'import paho.mqtt.client as mqtt',
            '',
            'BROKER = "127.0.0.1"',
            'PORT = 1883',
            'TOPIC = "kodingindonesia/fsiot/esp32-meja-01/telemetry"',
            'TARGET = 10',
            'FOLDER = Path(__file__).resolve().parent',
            'CSV_PATH = FOLDER / "stasiun.csv"',
            'DB_PATH = FOLDER / "stasiun.db"',
            '',
            '',
            'def now_iso():',
            '    return datetime.now(timezone.utc).astimezone().isoformat(timespec="seconds")',
            '',
            '',
            'def siapkan_penyimpanan():',
            '    if not CSV_PATH.exists():',
            '        with CSV_PATH.open("w", newline="", encoding="utf-8") as handle:',
            '            csv.writer(handle).writerow(',
            '                ["received_at", "device_id", "temperature_c", "humidity_pct", "topic", "payload"]',
            '            )',
            '    with sqlite3.connect(DB_PATH) as db:',
            '        db.execute(',
            '            """',
            '            CREATE TABLE IF NOT EXISTS telemetry (',
            '                id INTEGER PRIMARY KEY AUTOINCREMENT,',
            '                received_at TEXT NOT NULL,',
            '                device_id TEXT,',
            '                temperature_c REAL,',
            '                humidity_pct REAL,',
            '                topic TEXT,',
            '                payload TEXT',
            '            )',
            '            """',
            '        )',
            '',
            '',
            'def simpan(device_id, temperature_c, humidity_pct, topic, payload):',
            '    received_at = now_iso()',
            '    with CSV_PATH.open("a", newline="", encoding="utf-8") as handle:',
            '        csv.writer(handle).writerow(',
            '            [received_at, device_id, temperature_c, humidity_pct, topic, payload]',
            '        )',
            '    with sqlite3.connect(DB_PATH) as db:',
            '        db.execute(',
            '            """',
            '            INSERT INTO telemetry (received_at, device_id, temperature_c, humidity_pct, topic, payload)',
            '            VALUES (?, ?, ?, ?, ?, ?)',
            '            """,',
            '            (received_at, device_id, temperature_c, humidity_pct, topic, payload),',
            '        )',
            '',
            '',
            'def on_connect(client, userdata, connect_flags, reason_code, properties):',
            '    if reason_code.is_failure:',
            '        print("MQTT belum tersambung:", reason_code)',
            '        return',
            '    print("MQTT tersambung.")',
            '    client.subscribe(TOPIC)',
            '    print("Berlangganan:", TOPIC)',
            '',
            '',
            'def on_message(client, userdata, msg):',
            '    payload = msg.payload.decode("utf-8", errors="replace")',
            '    try:',
            '        data = json.loads(payload)',
            '    except json.JSONDecodeError:',
            '        print("JSON belum dikenali:", payload)',
            '        return',
            '    device_id = data.get("device_id", "tidak-ada")',
            '    temperature_c = data.get("temperature_c")',
            '    humidity_pct = data.get("humidity_pct")',
            '    print("Diterima:", payload)',
            '    simpan(device_id, temperature_c, humidity_pct, msg.topic, payload)',
            '    userdata["count"] += 1',
            '    print("Baris", userdata["count"], "/", TARGET)',
            '    if userdata["count"] >= TARGET:',
            '        print("10 baris tersimpan. Script selesai.")',
            '        client.disconnect()',
            '',
            '',
            'def main():',
            '    siapkan_penyimpanan()',
            '    userdata = {"count": 0}',
            '    client = mqtt.Client(',
            '        callback_api_version=mqtt.CallbackAPIVersion.VERSION2,',
            '        client_id="fsiot-fs40-terima",',
            '        userdata=userdata,',
            '    )',
            '    client.on_connect = on_connect',
            '    client.on_message = on_message',
            '    print("Menyambung ke", BROKER, "port", PORT)',
            '    print("Tekan Ctrl+C untuk berhenti lebih awal.")',
            '    try:',
            '        client.connect(BROKER, PORT, keepalive=60)',
            '    except OSError as error:',
            '        print("Broker belum terbuka di", BROKER, PORT)',
            '        print(error)',
            '        raise SystemExit(1) from error',
            '    try:',
            '        client.loop_forever()',
            '    except KeyboardInterrupt:',
            '        print("Dihentikan. Jalankan lihat_db.py untuk membaca yang sudah tersimpan.")',
            '        client.disconnect()',
            '',
            '',
            'if __name__ == "__main__":',
            '    main()',
        ]);
    }

    private function kirim(): string
    {
        return implode("\n", [
            'import json',
            'import time',
            '',
            'import paho.mqtt.client as mqtt',
            '',
            'BROKER = "127.0.0.1"',
            'PORT = 1883',
            'TOPIC = "kodingindonesia/fsiot/esp32-meja-01/telemetry"',
            '',
            'client = mqtt.Client(',
            '    callback_api_version=mqtt.CallbackAPIVersion.VERSION2,',
            '    client_id="fsiot-fs40-kirim",',
            ')',
            'try:',
            '    client.connect(BROKER, PORT, keepalive=60)',
            'except OSError as error:',
            '    print("Broker belum terbuka di", BROKER, PORT)',
            '    print(error)',
            '    raise SystemExit(1) from error',
            '',
            'client.loop_start()',
            'for i in range(10):',
            '    data = {',
            '        "device_id": "esp32-meja-01",',
            '        "temperature_c": round(27.0 + i * 0.4, 1),',
            '        "humidity_pct": round(60.0 + i, 1),',
            '    }',
            '    payload = json.dumps(data)',
            '    client.publish(TOPIC, payload, qos=0)',
            '    print("Terkirim:", payload)',
            '    time.sleep(0.4)',
            'client.loop_stop()',
            'client.disconnect()',
            'print("10 pesan contoh sudah dikirim.")',
        ]);
    }

    private function lihat(): string
    {
        return implode("\n", [
            'import sqlite3',
            'from pathlib import Path',
            '',
            'DB_PATH = Path(__file__).resolve().parent / "stasiun.db"',
            'if not DB_PATH.exists():',
            '    print("Berkas stasiun.db belum ada. Jalankan terima_stasiun.py dulu.")',
            '    raise SystemExit(1)',
            '',
            'with sqlite3.connect(DB_PATH) as db:',
            '    rows = db.execute(',
            '        "SELECT id, received_at, device_id, temperature_c, humidity_pct FROM telemetry ORDER BY id"',
            '    ).fetchall()',
            '',
            'print("Jumlah baris:", len(rows))',
            'print("id | received_at | device_id | temperature_c | humidity_pct")',
            'for row in rows[-10:]:',
            '    print(row[0], "|", row[1], "|", row[2], "|", row[3], "|", row[4])',
        ]);
    }

    private function aturan(): string
    {
        return implode("\n", [
            'import json',
            '',
            'import paho.mqtt.client as mqtt',
            '',
            'BROKER = "127.0.0.1"',
            'PORT = 1883',
            'TOPIC_TELEMETRY = "kodingindonesia/fsiot/esp32-meja-01/telemetry"',
            'TOPIC_COMMAND = "kodingindonesia/fsiot/esp32-meja-01/command"',
            'AMBANG = 30.0',
            '',
            '',
            'def on_connect(client, userdata, connect_flags, reason_code, properties):',
            '    if reason_code.is_failure:',
            '        print("MQTT belum tersambung:", reason_code)',
            '        return',
            '    print("MQTT tersambung.")',
            '    client.subscribe(TOPIC_TELEMETRY)',
            '',
            '',
            'def on_message(client, userdata, msg):',
            '    try:',
            '        data = json.loads(msg.payload.decode("utf-8"))',
            '    except json.JSONDecodeError:',
            '        print("JSON belum dikenali.")',
            '        return',
            '    suhu = data.get("temperature_c")',
            '    if suhu is None:',
            '        return',
            '    relay = "on" if suhu > AMBANG else "off"',
            '    perintah = json.dumps({"device_id": "esp32-meja-01", "relay": relay})',
            '    client.publish(TOPIC_COMMAND, perintah)',
            '    print("suhu", suhu, "->", perintah)',
            '',
            '',
            'client = mqtt.Client(',
            '    callback_api_version=mqtt.CallbackAPIVersion.VERSION2,',
            '    client_id="fsiot-fs40-aturan",',
            ')',
            'client.on_connect = on_connect',
            'client.on_message = on_message',
            'print("Aturan pelengkap Node-RED. Ambang", AMBANG)',
            'print("Tekan Ctrl+C untuk berhenti.")',
            'try:',
            '    client.connect(BROKER, PORT, keepalive=60)',
            'except OSError as error:',
            '    print("Broker belum terbuka di", BROKER, PORT)',
            '    print(error)',
            '    raise SystemExit(1) from error',
            'client.loop_forever()',
        ]);
    }

    private function body(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $terima = htmlspecialchars($this->terima(), ENT_QUOTES, 'UTF-8');
        $kirim = htmlspecialchars($this->kirim(), ENT_QUOTES, 'UTF-8');
        $lihat = htmlspecialchars($this->lihat(), ENT_QUOTES, 'UTF-8');
        $aturan = htmlspecialchars($this->aturan(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs40-tools-order.png', 'Urutan lima langkah: browser, MQTTX, Notepad, PowerShell, lalu kirim pesan', '<strong>Urutan meja kerja (lima langkah):</strong> browser → MQTTX Connect 127.0.0.1:1883 → Notepad menulis berkas → PowerShell <code>pip install -r</code> lalu subscriber → satu pesan MQTTX, lalu 10 contoh. Diagram buatan Koding Indonesia (FS-40).');
        $why = $this->figure('fs40-why-python.png', 'Perbandingan Node-RED sebagai otak visual dan Python sebagai gudang SQLite', '<strong>Python menyimpan. Node-RED tetap otak visual.</strong> Jangan hapus alur FS-38. Diagram buatan Koding Indonesia (FS-40).');
        $mqttx = $this->figure('fs40-mqttx.png', 'Ilustrasi MQTTX tersambung ke 127.0.0.1 port 1883 siap publish telemetry', '<strong>MQTTX sudah tersambung ke komputer ini.</strong> Host <code>127.0.0.1</code>, port <code>1883</code>. Belum tekan Publish sampai subscriber mencetak <code>MQTT tersambung.</code> Ilustrasi buatan Koding Indonesia (FS-40), meniru aplikasi <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX</a> oleh EMQ (Apache License 2.0). Tangkapan layar resmi tidak dipakai utuh.');
        $pip = $this->figure('fs40-pip-venv.png', 'Alur kiri ke kanan: folder fsiot-fs39, venv, pip install -r, paho-mqtt 2.1.0', '<strong>venv yang sama, versi dikunci.</strong> Baca dari kiri ke kanan: folder → venv FS-39 → pip → <code>paho-mqtt==2.1.0</code>. Diagram buatan Koding Indonesia (FS-40).');
        $callback = $this->figure('fs40-callback.png', 'Alur kiri ke kanan: broker, callback, cetak PowerShell, CSV, SQLite', '<strong>Gambar utama — pesan masuk, lalu disimpan.</strong> Baca dari kiri ke kanan: broker → callback → cetak → CSV → SQLite. Diagram buatan Koding Indonesia (FS-40).');
        $run = $this->figure('fs40-script-run.png', 'Ilustrasi PowerShell menampilkan MQTT tersambung dan baris pertama Diterima', '<strong>PowerShell sudah menampilkan MQTT tersambung.</strong> Biarkan jendela ini terbuka. Ilustrasi buatan Koding Indonesia (FS-40), meniru jendela PowerShell. Bukan screenshot jendela resmi.');
        $sqlite = $this->figure('fs40-sqlite.png', 'Keluaran lihat_db.py menampilkan jumlah baris 10 dan cuplikan tabel telemetry', '<strong>Bukti berhasil — 10 baris di SQLite.</strong> Angka suhu boleh berbeda. Yang dikunci adalah jumlah baris. Diagram buatan Koding Indonesia (FS-40).');
        $bonus = $this->figure('fs40-rules-bonus.png', 'Alur bonus: telemetry, if suhu lebih dari 30, publish command relay on', '<strong>Bonus — aturan Python pelengkap Node-RED.</strong> Tidak wajib untuk lulus hari ini. Diagram buatan Koding Indonesia (FS-40).');
        $troubleshooting = $this->figure('fs40-troubleshooting.png', 'Empat pemeriksaan: Mosquitto, host 127.0.0.1, venv, script yang langsung keluar', '<strong>Skema bantu.</strong> MQTTX dan Python harus ke broker yang sama. Topic harus sama persis dengan FS-34. Diagram buatan Koding Indonesia (FS-40).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan ketik pip dulu.'],
            ['title' => 'Buka MQTTX', 'text' => 'New Connection bernama <code>FS40 simpan PC</code>. Host <code>127.0.0.1</code>, Port <code>1883</code>, Connect. Siapkan topic telemetry. Jangan Publish dulu.'],
            ['title' => 'Buka Notepad, tulis berkas', 'text' => 'Simpan <code>requirements.txt</code>, <code>terima_stasiun.py</code>, <code>kirim_contoh.py</code>, dan <code>lihat_db.py</code> di folder <code>Documents\\fsiot-fs39</code>. All files, bukan <code>.txt</code>.'],
            ['title' => 'Buka PowerShell, pasang pustaka', 'text' => 'Start → ketik PowerShell. Tidak perlu <em>Run as administrator</em>. Tempel <code>pip install -r requirements.txt</code> memakai python di venv.'],
            ['title' => 'Jalankan subscriber, lalu kirim pesan', 'text' => 'Tunggu <code>MQTT tersambung.</code> Publish satu JSON di MQTTX. Lalu PowerShell baru untuk <code>kirim_contoh.py</code> sampai 10 baris.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = <code>lihat_db.py</code> mencetak <code>Jumlah baris: 10</code>. ESP32 boleh menyala, tetapi tidak wajib.');

        return <<<'HTML'
<h2>Pendahuluan — Python mulai menyimpan</h2>
<p><strong>FS-40 / #110 (ini)</strong> adalah lab penyimpanan pertama di fase FULLSTACK. Kemarin Python hanya bisa mencetak. Hari ini tugasnya lain: <strong>pesan stasiun masuk gudang di PC</strong>.</p>
<p><strong>Intinya:</strong> pasang <code>paho-mqtt==2.1.0</code> di venv folder yang sama, berlangganan Mosquitto lokal, cetak pesan, simpan CSV, lalu baca 10 baris SQLite.</p>
<p><strong>Analogi:</strong> Node-RED adalah papan pengumuman di kantor. Python adalah buku catatan di laci. Papan tetap dipasang. Hari ini kita mulai menulis buku itu.</p>
<p>Prasyarat lab: FS-39 (Python + venv) dan FS-34 (topic telemetry). Mosquitto + MQTTX dari fase CONNECTED. ESP32 <strong>boleh menyala</strong> dari FS-38, dan <strong>boleh dicabut</strong> — MQTTX bisa mengirim JSON yang sama. Tidak ada kabel baru, tidak ada Upload, tidak ada Flask, tidak ada MySQL.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Folder <code>Documents\fsiot-fs39</code> berisi <code>requirements.txt</code> dengan <code>paho-mqtt==2.1.0</code>.</li>
<li>pip di venv memasang <code>paho-mqtt==2.1.0</code> tanpa jendela Store.</li>
<li>PowerShell mencetak <code>MQTT tersambung.</code> lalu paling sedikit satu baris <code>Diterima:</code>.</li>
<li>Berkas <code>stasiun.csv</code> dan <code>stasiun.db</code> muncul di folder lab.</li>
<li><code>lihat_db.py</code> mencetak <code>Jumlah baris: 10</code>.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> belum Flask, belum MySQL, belum REST, belum dashboard. Bukti cukup = 10 baris SQLite. Aturan suhu di Python bersifat bonus.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Berlangganan</strong> — script mendaftar ke topic, lalu menunggu pesan. Di MQTTX tombolnya Subscribe; di Python namanya <code>subscribe</code>.</li>
<li><strong>Callback</strong> — fungsi yang dipanggil pustaka saat pesan tiba, misalnya <code>on_message</code>.</li>
<li><strong>paho-mqtt</strong> — pustaka Eclipse Paho untuk MQTT di Python. Versi lab dikunci 2.1.0.</li>
<li><strong>CSV</strong> — berkas teks berbaris, mudah dibuka. Hari ini nama berkasnya <code>stasiun.csv</code>.</li>
<li><strong>SQLite</strong> — basis data satu berkas. Hari ini nama berkasnya <code>stasiun.db</code>, tabel <code>telemetry</code>.</li>
<li><strong>Keep-alive</strong> — detak yang menjaga sambungan MQTT tetap hidup. Script memakai <code>loop_forever</code> supaya tidak langsung keluar.</li>
<li><strong>127.0.0.1</strong> — komputer ini sendiri. Python dan MQTTX di PC memakai alamat ini, seperti Node-RED. Bukan alamat ESP32.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> Flask, MySQL/MariaDB, Laragon, <code>php artisan</code>, Arduino IDE, AC 220V, broker publik, atau mengubah ExecutionPolicy. Node-RED FS-38 boleh tetap terbuka.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa Python menyimpan, bukan mengganti Node-RED</h2>
HTML
            .$why.<<<'HTML'
<p>Node-RED sudah memegang ambang visual. Yang belum ada adalah histori yang bisa dihitung nanti oleh API. SQLite adalah satu berkas di folder lab — cocok untuk satu PC.</p>
<p>Jangan mencampur: memasang paho-mqtt <strong>plus</strong> langsung menulis Flask. Satu langkah, satu bukti — hari ini buktinya 10 baris.</p>

<h2>Pastikan Mosquitto dan MQTTX</h2>
HTML
            .$mqttx.<<<'HTML'
<p>Jika jendela Mosquitto belum terbuka, <strong>buka dulu PowerShell</strong> lalu jalankan seperti FS-33:</p>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -v</code></pre>
<p><strong>Hasil yang dicari:</strong> jendela tetap terbuka dan terlihat angka <code>1883</code>. Biarkan terbuka selama praktik.</p>
<p><strong>Buka dulu MQTTX.</strong> New Connection, nama <code>FS40 simpan PC</code>. Host <code>127.0.0.1</code>, Port <code>1883</code>, Connect. Siapkan topic publish:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<p>Isi pesan JSON persis seperti ini:</p>
<pre><code>{"device_id":"esp32-meja-01","temperature_c":28.4,"humidity_pct":71.2}</code></pre>
<p><strong>Belum</strong> tekan Publish sampai PowerShell menulis <code>MQTT tersambung.</code></p>
<p>Kalau Mosquitto kamu masih memakai berkas listener LAN dari FS-34/FS-35 dan Python gagal ke <code>127.0.0.1</code>, ganti <code>BROKER</code> di script menjadi IPv4 PC dari <code>ipconfig</code> — angka yang sama dengan Host MQTTX pada lab ESP32. Jangan memakai alamat ESP32.</p>
<p><strong>macOS atau Linux:</strong> buka <strong>Terminal</strong>, jalankan <code>mosquitto -v</code> jika broker belum jalan, lalu MQTTX Host <code>127.0.0.1</code>.</p>

<h2>Tulis requirements.txt di folder lab</h2>
HTML
            .$pip.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>. Folder <code>.venv</code> dari FS-39 harus sudah ada. Jika belum, ulangi pembuatan venv di FS-39 sebelum pip.</p>
<p><strong>Buka dulu Notepad.</strong> Tempel satu baris ini. File → Save As, All files, nama <code>requirements.txt</code>, folder lab:</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p>Satu baris itu mengunci versi. Jangan <code>pip install paho-mqtt</code> tanpa angka; besok pustaka bisa berubah.</p>

<h2>Pasang paho-mqtt di venv</h2>
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>
<p><strong>Hasil yang dicari:</strong> pip menulis <code>Successfully installed</code> atau <code>Already satisfied</code> untuk <code>paho-mqtt</code>.</p>
<p>Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>. Perintah di atas sudah memakai <code>python.exe</code> di dalam venv. Rujukan: <a href="https://docs.python.org/3/tutorial/venv.html" target="_blank" rel="noopener noreferrer">venv — Python tutorial</a>.</p>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python -m pip install -r requirements.txt</code>.</p>

<h2>Tulis terima_stasiun.py</h2>
HTML
            .$callback.<<<'HTML'
<p><strong>Buka dulu Notepad.</strong> Tempel kode ini. Save As, All files, nama <code>terima_stasiun.py</code>, folder <code>Documents\fsiot-fs39</code>.</p>
<pre><code class="language-python">
HTML
            .$terima.<<<'HTML'
</code></pre>
<p><code>sqlite3</code>, <code>csv</code>, dan <code>json</code> adalah pustaka bawaan. Yang dipasang pip hanya <code>paho-mqtt</code>.</p>
<p>Baris <code>CallbackAPIVersion.VERSION2</code> wajib di paho-mqtt 2. Tanpa itu, script berhenti sebelum berlangganan. <code>loop_forever</code> menjaga keep-alive. Setelah 10 pesan JSON valid, script selesai sendiri.</p>

<h2>Jalankan subscriber, biarkan jendela terbuka</h2>
HTML
            .$run.<<<'HTML'
<p>Di PowerShell yang sama folder lab:</p>
<pre><code>.\.venv\Scripts\python.exe terima_stasiun.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>MQTT tersambung.</code> lalu <code>Berlangganan:</code> dan topic FS-34. Jendela ini <strong>tetap terbuka</strong>.</p>
<p>Kalau PowerShell menulis <code>Broker belum terbuka</code>: jendela Mosquitto belum jalan, atau <code>BROKER</code> perlu IPv4 PC.</p>

<h2>Kirim pesan: satu di MQTTX, lalu sepuluh contoh</h2>
<p>Kembali ke MQTTX. Pastikan topic dan JSON sudah diisi. Tekan <strong>Publish</strong> sekali.</p>
<p><strong>Hasil yang dicari di PowerShell:</strong> <code>Diterima:</code> lalu <code>Baris 1 / 10</code>.</p>
<p>Mengklik Publish sepuluh kali melelahkan. <strong>Buka PowerShell baru</strong> — jangan menutup subscriber. <strong>Buka dulu Notepad.</strong> Tempel kode ini. File → Save As, All files, nama <code>kirim_contoh.py</code>, folder <code>Documents\fsiot-fs39</code>:</p>
<pre><code class="language-python">
HTML
            .$kirim.<<<'HTML'
</code></pre>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe kirim_contoh.py</code></pre>
<p>Subscriber mencetak sampai <code>10 baris tersimpan. Script selesai.</code> ESP32 yang masih mengirim telemetry FS-34 juga dihitung; yang penting total 10 JSON valid.</p>

<h2>Baca SQLite dengan lihat_db.py</h2>
HTML
            .$sqlite.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong> di folder lab. Harus terlihat <code>stasiun.csv</code> dan <code>stasiun.db</code>.</p>
<p><strong>Buka dulu Notepad.</strong> Tempel kode ini. File → Save As, All files, nama <code>lihat_db.py</code>, folder <code>Documents\fsiot-fs39</code>:</p>
<pre><code class="language-python">
HTML
            .$lihat.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe lihat_db.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>Jumlah baris: 10</code> atau lebih. Ini tool sederhana hari ini — tidak perlu memasang DB Browser.</p>
<p>Rujukan: <a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a>.</p>

<h2>Bonus: aturan suhu di Python</h2>
HTML
            .$bonus.<<<'HTML'
<p>Tidak wajib. Kalau 10 baris sudah terbaca, lab utama selesai. Bonus ini pelengkap FS-38: jika <code>temperature_c</code> di atas <code>30</code>, Python mengirim perintah relay.</p>
<p><strong>Jangan jalankan berbarengan dengan Node-RED</strong> kecuali kamu sengaja membandingkan dua otak. Satu aturan cukup. ESP32 harus masih menjalankan firmware perintah FS-35/FS-38 agar relay berbunyi. <strong>Bukan AC 220V.</strong> Terminal NC/COM/NO tetap kosong.</p>
<pre><code class="language-python">
HTML
            .$aturan.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe aturan_stasiun.py</code></pre>
<p>Ubah <code>AMBANG</code> sesuai ruangan, seperti mengganti angka ambang di Node-RED. Payload command sama dengan FS-35: <code>{"device_id":"esp32-meja-01","relay":"on"}</code>.</p>

<h2>Jika baris tidak bertambah</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Jendela Mosquitto belum terbuka.</strong> Jalankan mosquitto seperti FS-33, biarkan terlihat <code>1883</code>.</li>
<li><strong>Host salah.</strong> Python di PC memakai <code>127.0.0.1</code>, bukan IP ESP32. MQTTX hari ini juga <code>127.0.0.1</code>.</li>
<li><strong>pip di luar venv.</strong> Pakai <code>.\.venv\Scripts\python.exe -m pip</code>. Kalau PowerShell menulis <code>No module named paho</code>, pustaka belum masuk kotak lab.</li>
<li><strong>Script langsung keluar.</strong> Jangan hapus <code>loop_forever</code>. Tunggu <code>MQTT tersambung.</code> sebelum Publish.</li>
<li><strong>Topic berbeda satu huruf.</strong> Salin persis <code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code>.</li>
</ol>

<h2 id="fsiot-sqlite-checklist">Checklist sebelum FS-42</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. FS-41 MariaDB boleh dilewati. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-sqlite-checklist-items">
<li>Folder <code>Documents\fsiot-fs39</code> masih berisi <code>.venv</code> dari FS-39.</li>
<li><code>requirements.txt</code> mengunci <code>paho-mqtt==2.1.0</code>.</li>
<li>pip install memakai <code>.venv\Scripts\python.exe -m pip</code>.</li>
<li>MQTTX Connect ke <code>127.0.0.1:1883</code>.</li>
<li><code>terima_stasiun.py</code> mencetak <code>MQTT tersambung.</code></li>
<li>Satu pesan MQTTX muncul sebagai <code>Diterima:</code>.</li>
<li>Berkas <code>stasiun.csv</code> bertambah.</li>
<li><code>lihat_db.py</code> menampilkan 10 baris.</li>
<li>Saya tidak mengubah ExecutionPolicy.</li>
<li>Saya tidak menghapus Node-RED — Python pelengkap.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: MQTTX → pip → subscriber → 10 baris. Pada FS-42, REST akan membaca SQLite ini. FS-41 MySQL bersifat opsional.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>pip ke Python global.</strong> Pakai python di dalam <code>.venv</code>.</li>
<li><strong>Mengubah ExecutionPolicy.</strong> Tetap pakai <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Host = IP ESP32.</strong> Subscriber PC bukan papan.</li>
<li><strong>Script tanpa loop.</strong> Subscribe lalu langsung selesai, pesan tidak sempat masuk.</li>
<li><strong>Flask atau MySQL hari ini.</strong> Ditunda. Jalur utama tetap SQLite.</li>
<li><strong>Menghapus flow Node-RED.</strong> Python tidak menggantinya.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa CSV dan SQLite?</h3>
<p>CSV mudah dibuka mata. SQLite siap dihitung oleh API. Keduanya ditulis dari callback yang sama supaya kamu melihat dua wujud data.</p>
<h3>Kenapa 127.0.0.1, bukan IPv4 ESP32?</h3>
<p>Python duduk di PC yang sama dengan broker. <code>127.0.0.1</code> artinya komputer ini. ESP32 tetap memakai IPv4 PC, karena papan itu perangkat lain.</p>
<h3>Apakah Node-RED dimatikan?</h3>
<p>Tidak. Node-RED tetap otak aturan visual. Python pelengkap penerima dan gudang.</p>
<h3>Python 3.13 boleh?</h3>
<p>Boleh, asal 3.11 atau lebih baru seperti FS-39.</p>
<h3>Kenapa script berhenti di 10?</h3>
<p>Supaya jendela tidak menggantung tanpa batas. Untuk menerima terus, naikkan <code>TARGET</code> atau biarkan sampai Ctrl+C.</p>
<h3>Apakah Flask hari ini?</h3>
<p>Tidak. Flask satu berkas diajarkan di FS-42, membaca SQLite yang baru kamu isi.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://eclipse.dev/paho/files/paho.mqtt.python/html/" target="_blank" rel="noopener noreferrer">Eclipse Paho Python documentation</a>. Eclipse Paho adalah proyek Eclipse Foundation.</li>
<li><a href="https://pypi.org/project/paho-mqtt/2.1.0/" target="_blank" rel="noopener noreferrer">paho-mqtt 2.1.0 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — DB-API 2.0 interface for SQLite databases</a></li>
<li><a href="https://docs.python.org/3/library/csv.html" target="_blank" rel="noopener noreferrer">csv — CSV File Reading and Writing</a></li>
<li><a href="https://docs.python.org/3/library/json.html" target="_blank" rel="noopener noreferrer">json — JSON encoder and decoder</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX</a> oleh EMQ, Apache License 2.0.</li>
<li><a href="https://mosquitto.org/" target="_blank" rel="noopener noreferrer">Eclipse Mosquitto</a></li>
<li>Diagram urutan tools, pelengkap Node-RED, venv, callback, SQLite, bonus aturan, dan skema periksa — Koding Indonesia (FS-40).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> Python sudah menyimpan 10 baris stasiun. <strong>FS-41</strong> MySQL bersifat opsional dan boleh dilewati. Pada <strong>FS-42</strong>, Flask satu berkas membaca SQLite ini dan meneruskan perintah MQTT — pelengkap Node-RED, bukan pengganti lab hari ini.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $terima = htmlspecialchars($this->terima(), ENT_QUOTES, 'UTF-8');
        $kirim = htmlspecialchars($this->kirim(), ENT_QUOTES, 'UTF-8');
        $lihat = htmlspecialchars($this->lihat(), ENT_QUOTES, 'UTF-8');
        $aturan = htmlspecialchars($this->aturan(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs40-tools-order.png', 'Five-step order: browser, MQTTX, Notepad, PowerShell, then send messages', '<strong>Desk order (five steps):</strong> browser → MQTTX Connect 127.0.0.1:1883 → Notepad writes the files → PowerShell <code>pip install -r</code> then the subscriber → one MQTTX message, then 10 samples. Diagram by Koding Indonesia (FS-40).');
        $why = $this->figure('fs40-why-python.png', 'Comparison of Node-RED as the visual brain and Python as the SQLite store', '<strong>Python stores. Node-RED stays the visual brain.</strong> Do not delete the FS-38 flow. Diagram by Koding Indonesia (FS-40).');
        $mqttx = $this->figure('fs40-mqttx.png', 'Illustration of MQTTX connected to 127.0.0.1 port 1883 ready to publish telemetry', '<strong>MQTTX is already connected to this computer.</strong> Host <code>127.0.0.1</code>, port <code>1883</code>. Do not press Publish until the subscriber prints <code>MQTT tersambung.</code> Illustration by Koding Indonesia (FS-40), modelled on <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX</a> by EMQ (Apache License 2.0). The official window screenshot is not used as-is.');
        $pip = $this->figure('fs40-pip-venv.png', 'Left-to-right flow: fsiot-fs39 folder, venv, pip install -r, paho-mqtt 2.1.0', '<strong>Same venv, pinned version.</strong> Read left to right: folder → FS-39 venv → pip → <code>paho-mqtt==2.1.0</code>. Diagram by Koding Indonesia (FS-40).');
        $callback = $this->figure('fs40-callback.png', 'Left-to-right flow: broker, callback, PowerShell print, CSV, SQLite', '<strong>Main figure — a message arrives, then it is stored.</strong> Read left to right: broker → callback → print → CSV → SQLite. Diagram by Koding Indonesia (FS-40).');
        $run = $this->figure('fs40-script-run.png', 'PowerShell illustration showing MQTT tersambung and the first Diterima line', '<strong>PowerShell is already showing MQTT tersambung.</strong> Leave this window open. Illustration by Koding Indonesia (FS-40), modelled on a PowerShell window. The official window screenshot is not used.');
        $sqlite = $this->figure('fs40-sqlite.png', 'lihat_db.py output showing 10 rows and a telemetry table excerpt', '<strong>Proof of success — 10 rows in SQLite.</strong> Temperature numbers may differ. The lock is the row count. Diagram by Koding Indonesia (FS-40).');
        $bonus = $this->figure('fs40-rules-bonus.png', 'Bonus flow: telemetry, if temperature above 30, publish relay on command', '<strong>Bonus — Python rules that complement Node-RED.</strong> Not required to pass today. Diagram by Koding Indonesia (FS-40).');
        $troubleshooting = $this->figure('fs40-troubleshooting.png', 'Four checks: Mosquitto, host 127.0.0.1, venv, a script that exits at once', '<strong>Helper schematic.</strong> MQTTX and Python must use the same broker. The topic must match FS-34 exactly. Diagram by Koding Indonesia (FS-40).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not type pip yet.'],
            ['title' => 'Open MQTTX', 'text' => 'New Connection named <code>FS40 simpan PC</code>. Host <code>127.0.0.1</code>, Port <code>1883</code>, Connect. Prepare the telemetry topic. Do not Publish yet.'],
            ['title' => 'Open Notepad, write the files', 'text' => 'Save <code>requirements.txt</code>, <code>terima_stasiun.py</code>, <code>kirim_contoh.py</code>, and <code>lihat_db.py</code> in <code>Documents\\fsiot-fs39</code>. All files, not <code>.txt</code>.'],
            ['title' => 'Open PowerShell, install the library', 'text' => 'Start → type PowerShell. You do not need <em>Run as administrator</em>. Paste <code>pip install -r requirements.txt</code> using the venv Python.'],
            ['title' => 'Run the subscriber, then send messages', 'text' => 'Wait for <code>MQTT tersambung.</code> Publish one JSON in MQTTX. Then a new PowerShell for <code>kirim_contoh.py</code> until 10 rows.'],
        ], '<strong>How to test today:</strong> success = <code>lihat_db.py</code> prints <code>Jumlah baris: 10</code>. The ESP32 may be on, but it is not required.');

        return <<<'HTML'
<h2>Introduction — Python starts storing</h2>
<p><strong>FS-40 / #110 (this article)</strong> is the first storage lab in the FULLSTACK phase. Yesterday Python could only print. Today the job is different: <strong>station messages enter a store on the PC</strong>.</p>
<p><strong>In short:</strong> install <code>paho-mqtt==2.1.0</code> in the same-folder venv, subscribe to local Mosquitto, print messages, save CSV, then read 10 SQLite rows.</p>
<p><strong>Analogy:</strong> Node-RED is the notice board in the office. Python is the notebook in the drawer. The board stays up. Today we start writing in the notebook.</p>
<p>Lab prerequisites: FS-39 (Python + venv) and FS-34 (telemetry topic). Mosquitto + MQTTX from the CONNECTED phase. The ESP32 <strong>may stay on</strong> from FS-38, or it may be unplugged — MQTTX can send the same JSON. No new cables, no Upload, no Flask, no MySQL.</p>

<h2>Expected outcome</h2>
<ul>
<li>The folder <code>Documents\fsiot-fs39</code> contains <code>requirements.txt</code> with <code>paho-mqtt==2.1.0</code>.</li>
<li>pip in the venv installs <code>paho-mqtt==2.1.0</code> without a Store window.</li>
<li>PowerShell prints <code>MQTT tersambung.</code> then at least one <code>Diterima:</code> line.</li>
<li>The files <code>stasiun.csv</code> and <code>stasiun.db</code> appear in the lab folder.</li>
<li><code>lihat_db.py</code> prints <code>Jumlah baris: 10</code>.</li>
</ul>
<p><strong>Lab limits today:</strong> no Flask, no MySQL, no REST, no dashboard. Proof = 10 SQLite rows. Python temperature rules are a bonus.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>Subscribe</strong> — the script registers on a topic and waits. In MQTTX the button is Subscribe; in Python the call is <code>subscribe</code>.</li>
<li><strong>Callback</strong> — a function the library calls when a message arrives, for example <code>on_message</code>.</li>
<li><strong>paho-mqtt</strong> — the Eclipse Paho MQTT library for Python. This lab pins 2.1.0.</li>
<li><strong>CSV</strong> — a text file of rows, easy to open. Today the file is <code>stasiun.csv</code>.</li>
<li><strong>SQLite</strong> — a one-file database. Today the file is <code>stasiun.db</code>, table <code>telemetry</code>.</li>
<li><strong>Keep-alive</strong> — a heartbeat that keeps the MQTT connection alive. The script uses <code>loop_forever</code> so it does not exit at once.</li>
<li><strong>127.0.0.1</strong> — this computer. Python and MQTTX on the PC use this address, like Node-RED. It is not the ESP32 address.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> Flask, MySQL/MariaDB, Laragon, <code>php artisan</code>, Arduino IDE, AC mains, a public broker, or changing ExecutionPolicy. Node-RED from FS-38 may stay open.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Why Python stores, instead of replacing Node-RED</h2>
HTML
            .$why.<<<'HTML'
<p>Node-RED already holds the visual threshold. What is still missing is a history that an API can count later. SQLite is one file in the lab folder — a fit for one PC.</p>
<p>Do not mix: installing paho-mqtt <strong>plus</strong> writing Flask at once. One step, one proof — today the proof is 10 rows.</p>

<h2>Make sure Mosquitto and MQTTX are ready</h2>
HTML
            .$mqttx.<<<'HTML'
<p>If the Mosquitto window is not open, <strong>open PowerShell first</strong> and run it as in FS-33:</p>
<pre><code>&amp; 'C:\Program Files\mosquitto\mosquitto.exe' -v</code></pre>
<p><strong>What to look for:</strong> the window stays open and shows <code>1883</code>. Leave it open during the lab.</p>
<p><strong>Open MQTTX first.</strong> New Connection, name <code>FS40 simpan PC</code>. Host <code>127.0.0.1</code>, Port <code>1883</code>, Connect. Prepare this publish topic:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<p>Use this JSON payload exactly:</p>
<pre><code>{"device_id":"esp32-meja-01","temperature_c":28.4,"humidity_pct":71.2}</code></pre>
<p><strong>Do not</strong> press Publish until PowerShell writes <code>MQTT tersambung.</code></p>
<p>If your Mosquitto still uses the LAN listener file from FS-34/FS-35 and Python fails on <code>127.0.0.1</code>, change <code>BROKER</code> in the script to the PC IPv4 from <code>ipconfig</code> — the same number MQTTX used in the ESP32 labs. Do not use the ESP32 address.</p>
<p><strong>macOS or Linux:</strong> open <strong>Terminal</strong>, run <code>mosquitto -v</code> if the broker is not up, then MQTTX Host <code>127.0.0.1</code>.</p>

<h2>Write requirements.txt in the lab folder</h2>
HTML
            .$pip.<<<'HTML'
<p><strong>Open File Explorer first</strong>, go to <code>Documents\fsiot-fs39</code>. The <code>.venv</code> folder from FS-39 must already be there. If it is missing, repeat the venv step in FS-39 before pip.</p>
<p><strong>Open Notepad first.</strong> Paste this one line. File → Save As, All files, name <code>requirements.txt</code>, lab folder:</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p>That single line pins the version. Do not run <code>pip install paho-mqtt</code> without a number; the library can change tomorrow.</p>

<h2>Install paho-mqtt in the venv</h2>
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. When the text appears, press Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>
<p><strong>What to look for:</strong> pip writes <code>Successfully installed</code> or <code>Already satisfied</code> for <code>paho-mqtt</code>.</p>
<p>If <code>.\.venv\Scripts\Activate.ps1</code> is rejected, <strong>do not change ExecutionPolicy</strong>. The command above already uses <code>python.exe</code> inside the venv. Reference: <a href="https://docs.python.org/3/tutorial/venv.html" target="_blank" rel="noopener noreferrer">venv — Python tutorial</a>.</p>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python -m pip install -r requirements.txt</code>.</p>

<h2>Write terima_stasiun.py</h2>
HTML
            .$callback.<<<'HTML'
<p><strong>Open Notepad first.</strong> Paste this code. Save As, All files, name <code>terima_stasiun.py</code>, folder <code>Documents\fsiot-fs39</code>.</p>
<pre><code class="language-python">
HTML
            .$terima.<<<'HTML'
</code></pre>
<p><code>sqlite3</code>, <code>csv</code>, and <code>json</code> are built-in libraries. pip installs only <code>paho-mqtt</code>.</p>
<p>The <code>CallbackAPIVersion.VERSION2</code> line is required on paho-mqtt 2. Without it, the script stops before it subscribes. <code>loop_forever</code> keeps keep-alive. After 10 valid JSON messages, the script finishes by itself.</p>

<h2>Run the subscriber and leave the window open</h2>
HTML
            .$run.<<<'HTML'
<p>In PowerShell, still in the lab folder:</p>
<pre><code>.\.venv\Scripts\python.exe terima_stasiun.py</code></pre>
<p><strong>What to look for:</strong> <code>MQTT tersambung.</code> then <code>Berlangganan:</code> and the FS-34 topic. This window <strong>stays open</strong>.</p>
<p>If PowerShell writes <code>Broker belum terbuka</code>: Mosquitto is not running, or <code>BROKER</code> needs the PC IPv4.</p>

<h2>Send messages: one in MQTTX, then ten samples</h2>
<p>Go back to MQTTX. Make sure the topic and JSON are filled. Press <strong>Publish</strong> once.</p>
<p><strong>What to look for in PowerShell:</strong> <code>Diterima:</code> then <code>Baris 1 / 10</code>.</p>
<p>Clicking Publish ten times is tiring. <strong>Open a new PowerShell</strong> — do not close the subscriber. <strong>Open Notepad first.</strong> Paste this code. File → Save As, All files, name <code>kirim_contoh.py</code>, folder <code>Documents\fsiot-fs39</code>:</p>
<pre><code class="language-python">
HTML
            .$kirim.<<<'HTML'
</code></pre>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe kirim_contoh.py</code></pre>
<p>The subscriber prints until <code>10 baris tersimpan. Script selesai.</code> An ESP32 still sending FS-34 telemetry also counts; the lock is 10 valid JSON messages in total.</p>

<h2>Read SQLite with lihat_db.py</h2>
HTML
            .$sqlite.<<<'HTML'
<p><strong>Open File Explorer first</strong> in the lab folder. You should see <code>stasiun.csv</code> and <code>stasiun.db</code>.</p>
<p><strong>Open Notepad first.</strong> Paste this code. File → Save As, All files, name <code>lihat_db.py</code>, folder <code>Documents\fsiot-fs39</code>:</p>
<pre><code class="language-python">
HTML
            .$lihat.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe lihat_db.py</code></pre>
<p><strong>What to look for:</strong> <code>Jumlah baris: 10</code> or more. This is today’s simple tool — you do not need to install DB Browser.</p>
<p>Reference: <a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a>.</p>

<h2>Bonus: temperature rules in Python</h2>
HTML
            .$bonus.<<<'HTML'
<p>Not required. If 10 rows already read, the main lab is done. This bonus complements FS-38: if <code>temperature_c</code> is above <code>30</code>, Python sends a relay command.</p>
<p><strong>Do not run it together with Node-RED</strong> unless you are comparing two brains on purpose. One rule set is enough. The ESP32 must still run the FS-35/FS-38 command firmware for the relay to click. <strong>Not AC mains.</strong> Leave NC/COM/NO empty.</p>
<pre><code class="language-python">
HTML
            .$aturan.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe aturan_stasiun.py</code></pre>
<p>Change <code>AMBANG</code> to match the room, like changing the threshold number in Node-RED. The command payload matches FS-35: <code>{"device_id":"esp32-meja-01","relay":"on"}</code>.</p>

<h2>If rows do not increase</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>The Mosquitto window is not open.</strong> Start mosquitto as in FS-33 and leave <code>1883</code> visible.</li>
<li><strong>Wrong host.</strong> Python on the PC uses <code>127.0.0.1</code>, not the ESP32 IP. MQTTX today also uses <code>127.0.0.1</code>.</li>
<li><strong>pip outside the venv.</strong> Use <code>.\.venv\Scripts\python.exe -m pip</code>. If PowerShell writes <code>No module named paho</code>, the library is not in the lab box.</li>
<li><strong>The script exits at once.</strong> Do not remove <code>loop_forever</code>. Wait for <code>MQTT tersambung.</code> before Publish.</li>
<li><strong>The topic differs by one letter.</strong> Copy <code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code> exactly.</li>
</ol>

<h2 id="fsiot-sqlite-checklist">Checklist before FS-42</h2>
<p>Tick an item only after you have actually done it. Target: <strong>10/10</strong>. FS-41 MariaDB may be skipped. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-sqlite-checklist-items">
<li>The folder <code>Documents\fsiot-fs39</code> still contains the FS-39 <code>.venv</code>.</li>
<li><code>requirements.txt</code> pins <code>paho-mqtt==2.1.0</code>.</li>
<li>pip install uses <code>.venv\Scripts\python.exe -m pip</code>.</li>
<li>MQTTX Connects to <code>127.0.0.1:1883</code>.</li>
<li><code>terima_stasiun.py</code> prints <code>MQTT tersambung.</code></li>
<li>One MQTTX message appears as <code>Diterima:</code>.</li>
<li>The file <code>stasiun.csv</code> grows.</li>
<li><code>lihat_db.py</code> shows 10 rows.</li>
<li>I did not change ExecutionPolicy.</li>
<li>I did not delete Node-RED — Python complements it.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: MQTTX → pip → subscriber → 10 rows. In FS-42, REST will read this SQLite file. FS-41 MySQL is optional.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>pip into global Python.</strong> Use the Python inside <code>.venv</code>.</li>
<li><strong>Changing ExecutionPolicy.</strong> Keep using <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Host = the ESP32 IP.</strong> The PC subscriber is not the board.</li>
<li><strong>A script with no loop.</strong> It subscribes then exits, so messages never arrive.</li>
<li><strong>Flask or MySQL today.</strong> That waits. The main path stays SQLite.</li>
<li><strong>Deleting the Node-RED flow.</strong> Python does not replace it.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why both CSV and SQLite?</h3>
<p>CSV is easy to read by eye. SQLite is ready for an API to count. Both are written from the same callback so you see two shapes of the data.</p>
<h3>Why 127.0.0.1, not the ESP32 IPv4?</h3>
<p>Python sits on the same PC as the broker. <code>127.0.0.1</code> means this computer. The ESP32 still uses the PC IPv4, because that board is a different device.</p>
<h3>Do I turn Node-RED off?</h3>
<p>No. Node-RED stays the visual rule brain. Python complements it as receiver and store.</p>
<h3>Is Python 3.13 allowed?</h3>
<p>Yes, as long as it is 3.11 or newer, as in FS-39.</p>
<h3>Why does the script stop at 10?</h3>
<p>So the window does not hang forever. To keep receiving, raise <code>TARGET</code> or wait until Ctrl+C.</p>
<h3>Is Flask today?</h3>
<p>No. One-file Flask is taught in FS-42, reading the SQLite file you just filled.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://eclipse.dev/paho/files/paho.mqtt.python/html/" target="_blank" rel="noopener noreferrer">Eclipse Paho Python documentation</a>. Eclipse Paho is an Eclipse Foundation project.</li>
<li><a href="https://pypi.org/project/paho-mqtt/2.1.0/" target="_blank" rel="noopener noreferrer">paho-mqtt 2.1.0 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — DB-API 2.0 interface for SQLite databases</a></li>
<li><a href="https://docs.python.org/3/library/csv.html" target="_blank" rel="noopener noreferrer">csv — CSV File Reading and Writing</a></li>
<li><a href="https://docs.python.org/3/library/json.html" target="_blank" rel="noopener noreferrer">json — JSON encoder and decoder</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX</a> by EMQ, Apache License 2.0.</li>
<li><a href="https://mosquitto.org/" target="_blank" rel="noopener noreferrer">Eclipse Mosquitto</a></li>
<li>Diagrams for tool order, the Node-RED complement, venv, the callback, SQLite, bonus rules, and the check schematic — Koding Indonesia (FS-40).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> Python now stores 10 station rows. <strong>FS-41</strong> MySQL is optional and may be skipped. In <strong>FS-42</strong>, one-file Flask reads this SQLite store and forwards MQTT commands — a complement to Node-RED, not a replacement for today’s lab.</p>
HTML;
    }
}
