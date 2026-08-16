<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article112Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-flask-rest-sqlite-stasiun';
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
            'title' => 'Pasang Flask di PC lalu baca histori stasiun lewat REST',
            'title_en' => 'Install Flask on the PC then read station history over REST',
            'excerpt' => 'FS-42 / #112: Flask satu berkas di venv FS-39, GET JSON dari stasiun.db, POST perintah ke Mosquitto. SQLite tetap gudang. Belum dashboard HTML.',
            'excerpt_en' => 'FS-42 / #112: one-file Flask in the FS-39 venv, GET JSON from stasiun.db, POST a command to Mosquitto. SQLite stays the store. No HTML dashboard.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Baca Histori SQLite lewat REST Flask — FS-42',
            'seo_title_en' => 'Read SQLite History through REST Flask — FS-42',
            'seo_description' => 'Lab pemula: pasang flask 3.1.3 di venv, buka GET /telemetry sampai JSON jumlah 10, kirim POST perintah MQTT. Bukan MySQL, bukan dashboard.',
            'seo_description_en' => 'A first lab: install flask 3.1.3 in the venv, open GET /telemetry until JSON shows 10 rows, POST an MQTT command. No MySQL, no dashboard.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'flask', 'mqtt', 'sqlite', 'esp32'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs42-cover-flask.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs42-cover-flask.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs42-cover-flask.webp',
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
            'TOPIC_COMMAND = "kodingindonesia/fsiot/esp32-meja-01/command"',
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
            '@app.get("/telemetry")',
            'def telemetry():',
            '    rows = baca_baris()',
            '    if rows is None:',
            '        return jsonify({"jumlah": 0, "pesan": "Berkas stasiun.db belum ada. Ulangi FS-40."}), 503',
            '    return jsonify({"jumlah": len(rows), "baris": rows[-10:]})',
            '',
            '',
            '@app.post("/command")',
            'def command():',
            '    data = request.get_json(silent=True) or {}',
            '    relay = str(data.get("relay", "")).lower()',
            '    device_id = str(data.get("device_id", "esp32-meja-01"))',
            '    if relay not in {"on", "off"}:',
            '        return jsonify({"ok": False, "pesan": "Isian relay harus on atau off."}), 400',
            '    payload = json.dumps({"device_id": device_id, "relay": relay})',
            '    client = mqtt.Client(',
            '        callback_api_version=mqtt.CallbackAPIVersion.VERSION2,',
            '        client_id="fsiot-fs42-pintu",',
            '    )',
            '    try:',
            '        client.connect(BROKER, MQTT_PORT, keepalive=60)',
            '    except OSError as error:',
            '        return jsonify({"ok": False, "pesan": "Broker belum terbuka di 127.0.0.1:1883", "error": str(error)}), 503',
            '    client.publish(TOPIC_COMMAND, payload)',
            '    client.disconnect()',
            '    return jsonify({"ok": True, "topic": TOPIC_COMMAND, "payload": payload})',
            '',
            '',
            'if __name__ == "__main__":',
            '    print("Pintu stasiun terbuka di http://127.0.0.1:5000")',
            '    print("GET  http://127.0.0.1:5000/telemetry")',
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
            'payload = {"device_id": "esp32-meja-01", "relay": "on"}',
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

    private function body(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $uji = htmlspecialchars($this->uji(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs42-tools-order.png', 'Urutan lima langkah: browser, MQTTX Subscribe command, Notepad, PowerShell Flask, browser GET JSON', '<strong>Urutan meja kerja (lima langkah):</strong> browser → MQTTX Connect 127.0.0.1:1883 lalu Subscribe topic command → Notepad menulis berkas → PowerShell <code>pip install -r</code> lalu jalankan Flask → browser GET JSON. Diagram buatan Koding Indonesia (FS-42).');
        $why = $this->figure('fs42-why-api.png', 'Perbandingan SQLite sebagai gudang dan Flask sebagai pintu HTTP GET dan POST', '<strong>SQLite tetap gudang. Flask hanya pintu.</strong> Dashboard HTML ditunda. Diagram buatan Koding Indonesia (FS-42).');
        $download = $this->figure('fs42-download.png', 'Alur kiri ke kanan: browser, dokumentasi Flask, pip flask 3.1.3, port 5000', '<strong>Pustaka dari PyPI, docs resmi dibaca dulu.</strong> Baca dari kiri ke kanan: browser → flask.palletsprojects.com → pip <code>flask==3.1.3</code> → pintu di port 5000. Diagram buatan Koding Indonesia (FS-42).');
        $mqttx = $this->figure('fs42-mqttx.png', 'Ilustrasi MQTTX Connected ke 127.0.0.1:1883 dan sudah Subscribe topic command', '<strong>MQTTX sudah berlangganan command.</strong> Connect dulu, baru Subscribe. Jangan Publish dulu. Ilustrasi buatan Koding Indonesia (FS-42), meniru <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX oleh EMQ</a> (Apache License 2.0). Tangkapan layar resmi tidak dipakai utuh.');
        $pip = $this->figure('fs42-pip-venv.png', 'Alur kiri ke kanan: folder fsiot-fs39, venv, pip install -r, flask 3.1.3', '<strong>venv yang sama, tambah satu baris terkunci.</strong> Baca dari kiri ke kanan: folder → venv FS-39 → pip → <code>flask==3.1.3</code>. Diagram buatan Koding Indonesia (FS-42).');
        $routes = $this->figure('fs42-routes.png', 'Alur kiri ke kanan: GET telemetry, Flask, SQLite, POST command, MQTTX', '<strong>Gambar utama — dua pintu, satu berkas Flask.</strong> Baca dari kiri ke kanan: GET JSON → SQLite; POST perintah → Mosquitto → MQTTX. Diagram buatan Koding Indonesia (FS-42).');
        $browser = $this->figure('fs42-browser-json.png', 'Ilustrasi browser menampilkan JSON jumlah 10 dari alamat 127.0.0.1:5000/telemetry', '<strong>Browser sudah menampilkan JSON.</strong> Yang dikunci adalah <code>"jumlah": 10</code>. Ilustrasi buatan Koding Indonesia (FS-42), meniru jendela browser. Tampilan resmi tidak dipakai utuh.');
        $post = $this->figure('fs42-post-mqtt.png', 'Alur kiri ke kanan: uji_perintah.py, Flask POST, Mosquitto 1883, MQTTX relay on', '<strong>POST perintah, MQTTX menampilkan JSON.</strong> Baca dari kiri ke kanan: script → Flask → broker → topic command. Diagram buatan Koding Indonesia (FS-42).');
        $troubleshooting = $this->figure('fs42-troubleshooting.png', 'Empat pemeriksaan: Flask belum terbuka, stasiun.db, Mosquitto 1883, port 5000', '<strong>Skema bantu.</strong> Flask ke <code>127.0.0.1:5000</code>. Jangan buka port ke internet. Diagram buatan Koding Indonesia (FS-42).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini dan <a href="https://flask.palletsprojects.com/" target="_blank" rel="noopener noreferrer">flask.palletsprojects.com</a>. Jangan ketik pip dulu.'],
            ['title' => 'Buka MQTTX', 'text' => 'Connect ke <code>127.0.0.1:1883</code>. Tekan Subscribe pada topic <code>kodingindonesia/fsiot/esp32-meja-01/command</code>. Jangan Publish dulu.'],
            ['title' => 'Buka Notepad, tulis berkas', 'text' => 'Simpan <code>requirements.txt</code>, <code>pintu_stasiun.py</code>, dan <code>uji_perintah.py</code> di folder <code>Documents\\fsiot-fs39</code>. All files, bukan <code>.txt</code>.'],
            ['title' => 'Buka PowerShell, pasang pustaka', 'text' => 'Start → ketik PowerShell. Tidak perlu <em>Run as administrator</em>. Tempel <code>pip install -r requirements.txt</code> memakai python di venv, lalu jalankan Flask.'],
            ['title' => 'Buka browser, uji pintu', 'text' => 'Di tab baru, buka <code>http://127.0.0.1:5000/telemetry</code>. Setelah JSON muncul, jendela PowerShell kedua menjalankan <code>uji_perintah.py</code>.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = browser menampilkan <code>"jumlah": 10</code> dan MQTTX menampilkan JSON <code>relay":"on"</code>. ESP32 boleh menyala, tetapi tidak wajib.');

        return <<<'HTML'
<h2>Pendahuluan — gudang punya pintu</h2>
<p><strong>FS-42 / #112 (ini)</strong> adalah lab REST pertama. Kemarin Python sudah mengisi berkas <code>stasiun.db</code>. Hari ini tugasnya lain: <strong>buka pintu HTTP di komputer ini</strong> supaya browser bisa membaca histori, dan perintah relay bisa dikirim lewat POST.</p>
<p><strong>Intinya:</strong> pasang <code>flask==3.1.3</code> di venv yang sama, jalankan satu berkas Flask, buka GET sampai JSON <code>"jumlah": 10</code>, lalu POST perintah ke Mosquitto. SQLite tidak dihapus.</p>
<p><strong>Analogi:</strong> SQLite adalah gudang. Flask adalah pintu. Orang di luar (browser) tidak masuk gudang; mereka ketuk pintu, pintu yang mengambil barang. Dashboard cantik belum dibangun — itu FS-44.</p>
<p>Prasyarat lab: FS-40 (<code>stasiun.db</code> berisi data), FS-39 (venv), Mosquitto + MQTTX dari FS-33. FS-41 MariaDB <strong>tidak wajib</strong>. ESP32 <strong>boleh menyala</strong>, dan <strong>boleh dicabut</strong> — bukti wajib hari ini adalah JSON + MQTTX, bukan klik relay. Tidak ada kabel baru, tidak ada Upload, <strong>Bukan AC 220V</strong>.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>MQTTX Connected ke <code>127.0.0.1:1883</code> dan sudah Subscribe topic command.</li>
<li>Folder <code>Documents\fsiot-fs39</code> berisi <code>requirements.txt</code> dengan <code>flask==3.1.3</code>.</li>
<li>PowerShell menampilkan <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>Browser di <code>http://127.0.0.1:5000/telemetry</code> menampilkan <code>"jumlah": 10</code>.</li>
<li><code>uji_perintah.py</code> mencetak <code>Perintah terkirim.</code></li>
<li>MQTTX menampilkan JSON <code>{"device_id":"esp32-meja-01","relay":"on"}</code>.</li>
<li>Berkas <code>stasiun.db</code> masih ada di folder lab.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> belum dashboard HTML, belum CORS, belum filter banyak perangkat, belum MySQL. Bukti cukup = JSON 10 baris + pesan command di MQTTX. Node-RED FS-38 boleh tetap terbuka sebagai otak visual; Flask adalah pintu kedua, bukan pengganti.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>REST</strong> — cara ketuk pintu lewat HTTP: GET untuk membaca, POST untuk mengirim perintah.</li>
<li><strong>API</strong> — pintu itu sendiri. Bukan halaman berwarna. Jawaban hari ini berbentuk JSON.</li>
<li><strong>JSON</strong> — teks bersiku kurung yang dibaca program. Di browser terlihat seperti <code>"jumlah": 10</code>.</li>
<li><strong>Flask</strong> — pustaka Python untuk membuat pintu HTTP. Lab ini satu berkas, keputusan terkunci, bukan FastAPI.</li>
<li><strong>GET /telemetry</strong> — alamat baca histori dari SQLite.</li>
<li><strong>POST /command</strong> — alamat kirim perintah. Flask meneruskannya ke topic MQTT command.</li>
<li><strong>127.0.0.1:5000</strong> — komputer ini, port Flask. Bukan 1883 (Mosquitto), bukan 3306 (MariaDB).</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC 220V, <code>file://</code>, membuka port 5000 ke internet, atau mengubah ExecutionPolicy. Thunder Client tidak wajib. Node-RED boleh tetap terbuka, tetapi jangan menjalankan bonus aturan Python FS-40 bersamaan kecuali kamu sengaja membandingkan dua otak.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa REST, bukan mengganti SQLite</h2>
HTML
            .$why.<<<'HTML'
<p>Gudang sudah ada sejak FS-40. Hari ini kita tidak pindah gudang. Kita hanya memasang pintu supaya program lain — mulai dari browser, nanti dashboard — tidak membuka berkas SQLite langsung.</p>
<p>Jangan mencampur: memasang Flask <strong>plus</strong> langsung menulis HTML cantik. Satu langkah, satu bukti — hari ini buktinya JSON di browser dan pesan command di MQTTX.</p>

<h2>Baca docs Flask, lalu pasang lewat pip</h2>
HTML
            .$download.<<<'HTML'
<p><strong>Buka browser.</strong> Buka <a href="https://flask.palletsprojects.com/" target="_blank" rel="noopener noreferrer">flask.palletsprojects.com</a>. Itu dokumentasi resmi (BSD-3-Clause). Flask <strong>tidak</strong> punya pemasang Windows seperti XAMPP. Yang dipasang nanti adalah satu baris pip terkunci.</p>
<p>Jangan <code>pip install flask</code> tanpa angka; besok pustaka bisa berubah. Jangan FastAPI, Bottle, atau Django hari ini.</p>

<h2>Nyalakan MQTTX, langganan topic command</h2>
HTML
            .$mqttx.<<<'HTML'
<p><strong>Buka dulu MQTTX.</strong> Host <code>127.0.0.1</code>, port <code>1883</code>, tekan Connect. Lalu Subscribe:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/command</code></pre>
<p><strong>Hasil yang dicari:</strong> status Connected, langganan command terlihat, daftar pesan masih kosong. Flask yang akan mengisi. Jangan tekan Publish.</p>
<p>Kalau Connect gagal: nyalakan Mosquitto seperti di FS-33. Python ke broker yang sama, <code>127.0.0.1</code>, bukan IPv4 ESP32.</p>

<h2>Tambah Flask di requirements.txt</h2>
HTML
            .$pip.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>. Folder <code>.venv</code> dan berkas <code>stasiun.db</code> dari FS-40 harus sudah ada. Jika venv belum ada, ulangi FS-39 sebelum pip. Jika <code>stasiun.db</code> belum ada, ulangi FS-40 sampai <code>lihat_db.py</code> menulis <code>Jumlah baris: 10</code>.</p>
<p><strong>Buka dulu Notepad.</strong> Tempel dua baris ini. File → Save As, All files, nama <code>requirements.txt</code>, folder lab. Baris paho tetap ada supaya venv FS-40 tidak kehilangan kunci. Baris <code>mysql-connector-python</code> dari FS-41 <strong>boleh tetap</strong> jika sudah ada — hari ini tidak dipakai.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p>Jangan <code>pip install Flask</code> ke Python global. Jangan mengganti angka <code>3.1.3</code>.</p>

<h2>Tulis pintu_stasiun.py</h2>
HTML
            .$routes.<<<'HTML'
<p><strong>Buka dulu Notepad.</strong> Tempel kode ini. Save As, All files, nama <code>pintu_stasiun.py</code>, folder <code>Documents\fsiot-fs39</code>.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>Baris <code>CallbackAPIVersion.VERSION2</code> wajib di paho-mqtt 2, sama seperti FS-40. <code>debug=False</code> supaya Flask tidak membuka dua proses. Flask <strong>hanya membaca</strong> SQLite; ia tidak menghapus <code>stasiun.db</code>.</p>

<h2>Jalankan Flask, buka GET di browser</h2>
HTML
            .$browser.<<<'HTML'
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe -m pip install -r requirements.txt
.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>Hasil yang dicari:</strong> pip menulis <code>Successfully installed</code> atau <code>Already satisfied</code> untuk Flask, lalu script menulis <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>. Jendela ini <strong>tetap terbuka</strong>.</p>
<p>Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>. Perintah di atas sudah memakai <code>python.exe</code> di dalam venv.</p>
<p><strong>Buka browser</strong> di tab baru. Ketik alamat ini, lalu Enter:</p>
<pre><code>http://127.0.0.1:5000/telemetry</code></pre>
<p><strong>Hasil yang dicari:</strong> teks JSON dengan <code>"jumlah": 10</code> atau lebih. Angka suhu boleh berbeda. Ini JSON, bukan halaman ber tombol. <strong>Jangan buka berkas HTML lewat <code>file://</code>.</strong></p>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python -m pip install -r requirements.txt</code> dan <code>.venv/bin/python pintu_stasiun.py</code>.</p>

<h2>Kirim perintah dengan uji_perintah.py</h2>
HTML
            .$post.<<<'HTML'
<p>Biarkan Flask berjalan di jendela pertama. <strong>Buka dulu PowerShell</strong> jendela kedua, folder lab yang sama. <strong>Buka dulu Notepad</strong>, simpan berkas ini sebagai <code>uji_perintah.py</code>:</p>
<pre><code class="language-python">
HTML
            .$uji.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe uji_perintah.py</code></pre>
<p><strong>Hasil yang dicari:</strong> script mencetak JSON berisi <code>"ok": true</code> lalu baris <code>Perintah terkirim.</code> Di MQTTX, pesan baru muncul di topic command.</p>
<p>Kalau ESP32 masih menjalankan firmware perintah dari lab sebelumnya, relay ikut. Kalau papan dicabut, MQTTX tetap cukup untuk lulus hari ini. Terminal NC/COM/NO tetap kosong. <strong>Bukan AC 220V.</strong></p>

<h2>Bonus: perintah off</h2>
<p>Tidak wajib. Kalau JSON 10 baris dan pesan <code>relay on</code> sudah terlihat, lab utama selesai. Bonus: di <code>uji_perintah.py</code> ganti <code>"on"</code> menjadi <code>"off"</code>, simpan, jalankan lagi. MQTTX menampilkan <code>"relay":"off"</code>.</p>
<p>Ini tidak menggantikan Node-RED. Dashboard HTML ditunda ke FS-44. <strong>Bukan AC 220V.</strong></p>

<h2>Jika JSON tidak muncul</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Flask belum terbuka.</strong> Jendela pertama harus tetap menampilkan <code>Pintu stasiun terbuka</code>. Jangan ditutup sebelum GET dan POST selesai.</li>
<li><strong>stasiun.db kosong atau hilang.</strong> Ulangi FS-40 sampai <code>lihat_db.py</code> menulis <code>Jumlah baris: 10</code>.</li>
<li><strong>Mosquitto belum 1883.</strong> GET masih bisa berhasil; POST yang gagal. Nyalakan broker, lalu Connect MQTTX lagi.</li>
<li><strong>Port 5000 dipakai.</strong> Tutup Flask lama, atau program lain yang memakai 5000. Jangan ganti port di script hari ini.</li>
<li><strong>pip di luar venv.</strong> Pakai <code>.\.venv\Scripts\python.exe -m pip</code>. Kalau PowerShell menulis <code>No module named flask</code>, pustaka belum masuk kotak lab.</li>
</ol>

<h2 id="fsiot-flask-checklist">Checklist sebelum FS-43</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-flask-checklist-items">
<li>MQTTX Connected ke 127.0.0.1:1883 dan Subscribe topic command.</li>
<li><code>requirements.txt</code> mengunci <code>flask==3.1.3</code>.</li>
<li>pip install memakai <code>.venv\Scripts\python.exe -m pip</code>.</li>
<li>Berkas <code>stasiun.db</code> masih ada di folder lab.</li>
<li>PowerShell menampilkan Pintu stasiun terbuka di port 5000.</li>
<li>Browser GET /telemetry menampilkan <code>"jumlah": 10</code>.</li>
<li><code>uji_perintah.py</code> mencetak Perintah terkirim.</li>
<li>MQTTX menampilkan JSON relay on.</li>
<li>Saya tidak mengubah ExecutionPolicy.</li>
<li>Saya tidak memakai MySQL hari ini — SQLite tetap gudang.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: MQTTX → pip → Flask → JSON 10 → perintah di MQTTX. Pada FS-43, pintu yang sama mulai membedakan <code>device_id</code>. MariaDB tetap opsional.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Menutup jendela Flask sebelum GET.</strong> Pintu harus tetap terbuka.</li>
<li><strong>pip ke Python global.</strong> Pakai python di dalam <code>.venv</code>.</li>
<li><strong>Mengubah ExecutionPolicy.</strong> Tetap pakai <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Membuka file://.</strong> JSON hanya muncul jika Flask yang menyajikan alamat <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Memaksa MySQL dulu.</strong> REST hari ini membaca SQLite.</li>
<li><strong>Membangun dashboard HTML hari ini.</strong> Ditunda ke FS-44.</li>
<li><strong>Membuka 5000 ke internet.</strong> Lab hanya <code>127.0.0.1</code>.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa bukan MySQL?</h3>
<p>SQLite sudah berisi 10 baris. FS-41 opsional. Capstone tidak mewajibkan MariaDB. Pintu REST membaca gudang yang sudah ada.</p>
<h3>CORS error di browser?</h3>
<p>Belum dibahas. Jangan buka HTML dari <code>file://</code>. Hari ini GET langsung ke alamat Flask, jadi browser dan API satu asal.</p>
<h3>Thunder Client atau curl wajib?</h3>
<p>Tidak. Browser untuk GET, <code>uji_perintah.py</code> untuk POST. curl.exe boleh, setelah PowerShell disebut, tetapi tidak dikunci.</p>
<h3>Kenapa Flask, bukan FastAPI?</h3>
<p>Keputusan kurikulum: Flask satu berkas. FastAPI boleh kamu pelajari nanti, bukan jalur utama lab ini.</p>
<h3>Apakah dashboard HTML hari ini?</h3>
<p>Tidak. JSON di browser adalah bukti pintu. Halaman angka dan tombol ada di FS-44.</p>
<h3>ESP32 wajib menyala?</h3>
<p>Tidak. MQTTX menampilkan perintah sudah cukup. Relay klik hanya jika firmware perintah lab sebelumnya masih jalan.</p>
<h3>Apakah stasiun.db dihapus?</h3>
<p>Tidak. Flask hanya membaca. Gudang tetap di folder.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://flask.palletsprojects.com/" target="_blank" rel="noopener noreferrer">Flask documentation</a> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://pypi.org/project/paho-mqtt/2.1.0/" target="_blank" rel="noopener noreferrer">paho-mqtt 2.1.0 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li>Diagram urutan tools, gudang versus pintu, docs, venv, dua rute, alur POST, dan skema periksa — Koding Indonesia (FS-42).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> Flask sudah membuka pintu REST di komputer ini. Browser membaca 10 baris SQLite, MQTTX menampilkan perintah. Pada <strong>FS-43</strong>, pintu yang sama mulai membedakan <code>device_id</code> supaya dua stasiun tidak tertukar. Dashboard HTML menunggu FS-44.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $pintu = htmlspecialchars($this->pintu(), ENT_QUOTES, 'UTF-8');
        $uji = htmlspecialchars($this->uji(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs42-tools-order.png', 'Five-step order: browser, MQTTX Subscribe command, Notepad, PowerShell Flask, browser GET JSON', '<strong>Desk order (five steps):</strong> browser → MQTTX Connect 127.0.0.1:1883 then Subscribe the command topic → Notepad writes the files → PowerShell <code>pip install -r</code> then run Flask → browser GET JSON. Diagram by Koding Indonesia (FS-42).');
        $why = $this->figure('fs42-why-api.png', 'Comparison of SQLite as the store and Flask as the HTTP GET and POST door', '<strong>SQLite stays the store. Flask is only the door.</strong> The HTML dashboard waits. Diagram by Koding Indonesia (FS-42).');
        $download = $this->figure('fs42-download.png', 'Left-to-right flow: browser, Flask docs, pip flask 3.1.3, port 5000', '<strong>The library comes from PyPI; read the official docs first.</strong> Read left to right: browser → flask.palletsprojects.com → pip <code>flask==3.1.3</code> → the door on port 5000. Diagram by Koding Indonesia (FS-42).');
        $mqttx = $this->figure('fs42-mqttx.png', 'MQTTX illustration Connected to 127.0.0.1:1883 and already Subscribed to the command topic', '<strong>MQTTX is already subscribed to command.</strong> Connect first, then Subscribe. Do not Publish yet. Illustration by Koding Indonesia (FS-42), modelled on <a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0). The official window screenshot is not used as-is.');
        $pip = $this->figure('fs42-pip-venv.png', 'Left-to-right flow: fsiot-fs39 folder, venv, pip install -r, flask 3.1.3', '<strong>Same venv, one extra pinned line.</strong> Read left to right: folder → FS-39 venv → pip → <code>flask==3.1.3</code>. Diagram by Koding Indonesia (FS-42).');
        $routes = $this->figure('fs42-routes.png', 'Left-to-right flow: GET telemetry, Flask, SQLite, POST command, MQTTX', '<strong>Main figure — two doors, one Flask file.</strong> Read left to right: GET JSON → SQLite; POST command → Mosquitto → MQTTX. Diagram by Koding Indonesia (FS-42).');
        $browser = $this->figure('fs42-browser-json.png', 'Browser illustration showing JSON jumlah 10 from 127.0.0.1:5000/telemetry', '<strong>The browser is already showing JSON.</strong> The lock is <code>"jumlah": 10</code>. Illustration by Koding Indonesia (FS-42), modelled on a browser window. The official window is not used as-is.');
        $post = $this->figure('fs42-post-mqtt.png', 'Left-to-right flow: uji_perintah.py, Flask POST, Mosquitto 1883, MQTTX relay on', '<strong>POST the command, MQTTX shows the JSON.</strong> Read left to right: script → Flask → broker → command topic. Diagram by Koding Indonesia (FS-42).');
        $troubleshooting = $this->figure('fs42-troubleshooting.png', 'Four checks: Flask not open, stasiun.db, Mosquitto 1883, port 5000', '<strong>Helper schematic.</strong> Flask uses <code>127.0.0.1:5000</code>. Do not open the port to the internet. Diagram by Koding Indonesia (FS-42).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide and <a href="https://flask.palletsprojects.com/" target="_blank" rel="noopener noreferrer">flask.palletsprojects.com</a> ready. Do not type pip yet.'],
            ['title' => 'Open MQTTX', 'text' => 'Connect to <code>127.0.0.1:1883</code>. Press Subscribe on topic <code>kodingindonesia/fsiot/esp32-meja-01/command</code>. Do not Publish yet.'],
            ['title' => 'Open Notepad, write the files', 'text' => 'Save <code>requirements.txt</code>, <code>pintu_stasiun.py</code>, and <code>uji_perintah.py</code> in <code>Documents\\fsiot-fs39</code>. All files, not <code>.txt</code>.'],
            ['title' => 'Open PowerShell, install the library', 'text' => 'Start → type PowerShell. You do not need <em>Run as administrator</em>. Paste <code>pip install -r requirements.txt</code> using the venv Python, then run Flask.'],
            ['title' => 'Open a browser, test the door', 'text' => 'In a new tab, open <code>http://127.0.0.1:5000/telemetry</code>. After the JSON appears, a second PowerShell window runs <code>uji_perintah.py</code>.'],
        ], '<strong>How to test today:</strong> success = the browser shows <code>"jumlah": 10</code> and MQTTX shows JSON <code>relay":"on"</code>. The ESP32 may be on, but it is not required.');

        return <<<'HTML'
<h2>Introduction — the store gets a door</h2>
<p><strong>FS-42 / #112 (this article)</strong> is the first REST lab. Yesterday Python already filled the file <code>stasiun.db</code>. Today the job is different: <strong>open an HTTP door on this computer</strong> so a browser can read the history, and a relay command can be sent with POST.</p>
<p><strong>In short:</strong> install <code>flask==3.1.3</code> in the same venv, run one Flask file, open GET until the JSON shows <code>"jumlah": 10</code>, then POST a command to Mosquitto. SQLite is not deleted.</p>
<p><strong>Analogy:</strong> SQLite is the store. Flask is the door. People outside (the browser) do not walk into the store; they knock, and the door fetches the goods. A pretty dashboard is not built yet — that is FS-44.</p>
<p>Lab prerequisites: FS-40 (<code>stasiun.db</code> has data), FS-39 (venv), Mosquitto + MQTTX from FS-33. FS-41 MariaDB is <strong>not required</strong>. The ESP32 <strong>may stay on</strong>, and <strong>may be unplugged</strong> — today’s required proof is JSON + MQTTX, not a relay click. No new cables, no Upload, <strong>Not AC mains</strong>.</p>

<h2>Expected outcome</h2>
<ul>
<li>MQTTX is Connected to <code>127.0.0.1:1883</code> and has Subscribed to the command topic.</li>
<li>Folder <code>Documents\fsiot-fs39</code> contains <code>requirements.txt</code> with <code>flask==3.1.3</code>.</li>
<li>PowerShell shows <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>.</li>
<li>The browser at <code>http://127.0.0.1:5000/telemetry</code> shows <code>"jumlah": 10</code>.</li>
<li><code>uji_perintah.py</code> prints <code>Perintah terkirim.</code></li>
<li>MQTTX shows JSON <code>{"device_id":"esp32-meja-01","relay":"on"}</code>.</li>
<li>The file <code>stasiun.db</code> is still in the lab folder.</li>
</ul>
<p><strong>Today’s lab boundary:</strong> no HTML dashboard, no CORS, no multi-device filter, no MySQL. Enough proof = JSON with 10 rows + a command message in MQTTX. Node-RED from FS-38 may stay open as the visual brain; Flask is a second door, not a replacement.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>REST</strong> — how you knock on the door over HTTP: GET to read, POST to send a command.</li>
<li><strong>API</strong> — the door itself. Not a colourful page. Today’s answers are JSON.</li>
<li><strong>JSON</strong> — bracketed text programs read. In the browser it looks like <code>"jumlah": 10</code>.</li>
<li><strong>Flask</strong> — the Python library that makes the HTTP door. This lab is one file, a locked decision, not FastAPI.</li>
<li><strong>GET /telemetry</strong> — the address that reads history from SQLite.</li>
<li><strong>POST /command</strong> — the address that sends a command. Flask forwards it to the MQTT command topic.</li>
<li><strong>127.0.0.1:5000</strong> — this computer, the Flask port. Not 1883 (Mosquitto), not 3306 (MariaDB).</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC mains, <code>file://</code>, opening port 5000 to the internet, or changing ExecutionPolicy. Thunder Client is not required. Node-RED may stay open, but do not run the FS-40 Python rules bonus at the same time unless you are comparing two brains on purpose.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Why REST, instead of replacing SQLite</h2>
HTML
            .$why.<<<'HTML'
<p>The store has existed since FS-40. Today we do not move the store. We only fit a door so other programs — starting with the browser, later a dashboard — do not open the SQLite file themselves.</p>
<p>Do not mix: installing Flask <strong>and</strong> immediately writing pretty HTML. One step, one proof — today that proof is JSON in the browser and a command message in MQTTX.</p>

<h2>Read the Flask docs, then install with pip</h2>
HTML
            .$download.<<<'HTML'
<p><strong>Open a browser.</strong> Open <a href="https://flask.palletsprojects.com/" target="_blank" rel="noopener noreferrer">flask.palletsprojects.com</a>. That is the official documentation (BSD-3-Clause). Flask does <strong>not</strong> have a Windows installer like XAMPP. What you install later is one pinned pip line.</p>
<p>Do not <code>pip install flask</code> without a version; the library can change tomorrow. Not FastAPI, Bottle, or Django today.</p>

<h2>Start MQTTX, subscribe to the command topic</h2>
HTML
            .$mqttx.<<<'HTML'
<p><strong>Open MQTTX first.</strong> Host <code>127.0.0.1</code>, port <code>1883</code>, press Connect. Then Subscribe:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/command</code></pre>
<p><strong>What you want:</strong> status Connected, the command subscription visible, the message list still empty. Flask will fill it. Do not press Publish.</p>
<p>If Connect fails: start Mosquitto as in FS-33. Python uses the same broker, <code>127.0.0.1</code>, not the ESP32 IPv4.</p>

<h2>Add Flask in requirements.txt</h2>
HTML
            .$pip.<<<'HTML'
<p><strong>Open File Explorer first</strong>, go to <code>Documents\fsiot-fs39</code>. The <code>.venv</code> folder and the <code>stasiun.db</code> file from FS-40 must already be there. If the venv is missing, repeat FS-39 before pip. If <code>stasiun.db</code> is missing, repeat FS-40 until <code>lihat_db.py</code> prints <code>Jumlah baris: 10</code>.</p>
<p><strong>Open Notepad first.</strong> Paste these two lines. File → Save As, All files, name <code>requirements.txt</code>, lab folder. Keep the paho line so the FS-40 venv does not lose its pin. A <code>mysql-connector-python</code> line from FS-41 <strong>may stay</strong> if it is already there — it is not used today.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p>Do not <code>pip install Flask</code> into global Python. Do not change the <code>3.1.3</code> number.</p>

<h2>Write pintu_stasiun.py</h2>
HTML
            .$routes.<<<'HTML'
<p><strong>Open Notepad first.</strong> Paste this code. Save As, All files, name <code>pintu_stasiun.py</code>, folder <code>Documents\fsiot-fs39</code>.</p>
<pre><code class="language-python">
HTML
            .$pintu.<<<'HTML'
</code></pre>
<p>The <code>CallbackAPIVersion.VERSION2</code> line is required on paho-mqtt 2, same as FS-40. <code>debug=False</code> keeps Flask from starting two processes. Flask <strong>only reads</strong> SQLite; it does not delete <code>stasiun.db</code>.</p>

<h2>Run Flask, open GET in the browser</h2>
HTML
            .$browser.<<<'HTML'
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste a command:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe -m pip install -r requirements.txt
.\.venv\Scripts\python.exe pintu_stasiun.py</code></pre>
<p><strong>What you want:</strong> pip writes <code>Successfully installed</code> or <code>Already satisfied</code> for Flask, then the script writes <code>Pintu stasiun terbuka di http://127.0.0.1:5000</code>. Leave this window <strong>open</strong>.</p>
<p>If <code>.\.venv\Scripts\Activate.ps1</code> is rejected, <strong>do not change ExecutionPolicy</strong>. The commands above already use <code>python.exe</code> inside the venv.</p>
<p><strong>Open a browser</strong> in a new tab. Type this address, then Enter:</p>
<pre><code>http://127.0.0.1:5000/telemetry</code></pre>
<p><strong>What you want:</strong> JSON text with <code>"jumlah": 10</code> or more. Temperature numbers may differ. This is JSON, not a page with buttons. <strong>Do not open an HTML file through <code>file://</code>.</strong></p>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python -m pip install -r requirements.txt</code> and <code>.venv/bin/python pintu_stasiun.py</code>.</p>

<h2>Send a command with uji_perintah.py</h2>
HTML
            .$post.<<<'HTML'
<p>Leave Flask running in the first window. <strong>Open PowerShell first</strong> in a second window, same lab folder. <strong>Open Notepad first</strong>, save this file as <code>uji_perintah.py</code>:</p>
<pre><code class="language-python">
HTML
            .$uji.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe uji_perintah.py</code></pre>
<p><strong>What you want:</strong> the script prints JSON containing <code>"ok": true</code> then the line <code>Perintah terkirim.</code> In MQTTX, a new message appears on the command topic.</p>
<p>If the ESP32 is still running the command firmware from the earlier lab, the relay follows. If the board is unplugged, MQTTX is still enough to pass today. Leave NC/COM/NO empty. <strong>Not AC mains.</strong></p>

<h2>Bonus: the off command</h2>
<p>Not required. If the 10-row JSON and the <code>relay on</code> message are already visible, the main lab is done. Bonus: in <code>uji_perintah.py</code> change <code>"on"</code> to <code>"off"</code>, save, run again. MQTTX shows <code>"relay":"off"</code>.</p>
<p>This does not replace Node-RED. The HTML dashboard waits for FS-44. <strong>Not AC mains.</strong></p>

<h2>If JSON does not appear</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Flask is not open.</strong> The first window must still show <code>Pintu stasiun terbuka</code>. Do not close it before GET and POST are done.</li>
<li><strong>stasiun.db is empty or missing.</strong> Repeat FS-40 until <code>lihat_db.py</code> prints <code>Jumlah baris: 10</code>.</li>
<li><strong>Mosquitto is not on 1883.</strong> GET can still succeed; POST is what fails. Start the broker, then Connect MQTTX again.</li>
<li><strong>Port 5000 is in use.</strong> Close the old Flask, or another program using 5000. Do not change the port in today’s script.</li>
<li><strong>pip outside the venv.</strong> Use <code>.\.venv\Scripts\python.exe -m pip</code>. If PowerShell writes <code>No module named flask</code>, the library is not in the lab box yet.</li>
</ol>

<h2 id="fsiot-flask-checklist">Checklist before FS-43</h2>
<p>Tick each item after you have actually done it. Target: <strong>10/10</strong>. Progress stays in this device’s browser and is not sent to the server.</p>
<ul id="fsiot-flask-checklist-items">
<li>MQTTX is Connected to 127.0.0.1:1883 and Subscribed to the command topic.</li>
<li><code>requirements.txt</code> pins <code>flask==3.1.3</code>.</li>
<li>pip install uses <code>.venv\Scripts\python.exe -m pip</code>.</li>
<li>The file <code>stasiun.db</code> is still in the lab folder.</li>
<li>PowerShell shows Pintu stasiun terbuka on port 5000.</li>
<li>Browser GET /telemetry shows <code>"jumlah": 10</code>.</li>
<li><code>uji_perintah.py</code> prints Perintah terkirim.</li>
<li>MQTTX shows the relay on JSON.</li>
<li>I did not change ExecutionPolicy.</li>
<li>I did not use MySQL today — SQLite stays the store.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: MQTTX → pip → Flask → JSON 10 → command in MQTTX. In FS-43, the same door starts telling <code>device_id</code> values apart. MariaDB stays optional.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Closing the Flask window before GET.</strong> The door must stay open.</li>
<li><strong>pip into global Python.</strong> Use the Python inside <code>.venv</code>.</li>
<li><strong>Changing ExecutionPolicy.</strong> Keep using <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Opening file://.</strong> JSON only appears when Flask serves <code>http://127.0.0.1:5000</code>.</li>
<li><strong>Forcing MySQL first.</strong> Today’s REST reads SQLite.</li>
<li><strong>Building an HTML dashboard today.</strong> That waits for FS-44.</li>
<li><strong>Opening 5000 to the internet.</strong> The lab is only <code>127.0.0.1</code>.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why not MySQL?</h3>
<p>SQLite already holds 10 rows. FS-41 is optional. Capstone does not require MariaDB. The REST door reads the store that already exists.</p>
<h3>CORS error in the browser?</h3>
<p>Not covered yet. Do not open HTML from <code>file://</code>. Today GET goes straight to the Flask address, so the browser and the API share one origin.</p>
<h3>Are Thunder Client or curl required?</h3>
<p>No. The browser is for GET, <code>uji_perintah.py</code> is for POST. curl.exe is allowed after PowerShell is named, but it is not locked.</p>
<h3>Why Flask, not FastAPI?</h3>
<p>Curriculum decision: one-file Flask. You may learn FastAPI later; it is not the main path for this lab.</p>
<h3>Is the HTML dashboard today?</h3>
<p>No. JSON in the browser proves the door. The numbers-and-buttons page is FS-44.</p>
<h3>Must the ESP32 stay on?</h3>
<p>No. MQTTX showing the command is enough. The relay clicks only if the earlier lab’s command firmware is still running.</p>
<h3>Is stasiun.db deleted?</h3>
<p>No. Flask only reads. The store stays in the folder.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://flask.palletsprojects.com/" target="_blank" rel="noopener noreferrer">Flask documentation</a> (BSD-3-Clause)</li>
<li><a href="https://pypi.org/project/Flask/3.1.3/" target="_blank" rel="noopener noreferrer">Flask 3.1.3 on PyPI</a></li>
<li><a href="https://pypi.org/project/paho-mqtt/2.1.0/" target="_blank" rel="noopener noreferrer">paho-mqtt 2.1.0 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li>Diagrams for tool order, store versus door, docs, venv, two routes, POST flow, and the check schematic — Koding Indonesia (FS-42).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> Flask has opened a REST door on this computer. The browser reads 10 SQLite rows, MQTTX shows the command. In <strong>FS-43</strong>, the same door starts telling <code>device_id</code> values apart so two stations are not mixed. The HTML dashboard waits for FS-44.</p>
HTML;
    }
}
