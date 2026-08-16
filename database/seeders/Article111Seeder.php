<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article111Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-mariadb-histori-sqlite-stasiun';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'python', 'mysql', 'sqlite', 'esp32'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Pasang MariaDB di PC lalu salin histori stasiun dari SQLite',
            'title_en' => 'Install MariaDB on the PC then copy station history from SQLite',
            'excerpt' => 'FS-41 / #111 opsional: XAMPP MariaDB di PC, phpMyAdmin, salin 10 baris dari stasiun.db. SQLite tetap jalur utama. Belum Flask.',
            'excerpt_en' => 'FS-41 / #111 optional: XAMPP MariaDB on the PC, phpMyAdmin, copy 10 rows from stasiun.db. SQLite stays the main path. No Flask.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Salin Histori SQLite ke MariaDB di PC — FS-41',
            'seo_title_en' => 'Copy SQLite History into MariaDB on the PC — FS-41',
            'seo_description' => 'Lab opsional: pasang XAMPP, nyalakan MariaDB, salin 10 baris telemetry dari SQLite. Capstone tidak mewajibkan MySQL. Belum Flask.',
            'seo_description_en' => 'Optional lab: install XAMPP, start MariaDB, copy 10 telemetry rows from SQLite. Capstone does not require MySQL. No Flask.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'mysql', 'sqlite', 'esp32'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs41-cover-mysql.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs41-cover-mysql.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs41-cover-mysql.webp',
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
        return "paho-mqtt==2.1.0\nmysql-connector-python==26.7.0";
    }

    private function salin(): string
    {
        return implode("\n", [
            'import sqlite3',
            'from pathlib import Path',
            '',
            'import mysql.connector',
            '',
            'HOST = "127.0.0.1"',
            'PORT = 3306',
            'USER = "root"',
            'PASSWORD = ""',
            'DATABASE = "stasiun"',
            'FOLDER = Path(__file__).resolve().parent',
            'DB_PATH = FOLDER / "stasiun.db"',
            '',
            'if not DB_PATH.exists():',
            '    print("Berkas stasiun.db belum ada. Ulangi FS-40, atau jalankan isi_contoh_mysql.py.")',
            '    raise SystemExit(1)',
            '',
            'try:',
            '    cnx = mysql.connector.connect(host=HOST, port=PORT, user=USER, password=PASSWORD)',
            'except mysql.connector.Error as error:',
            '    print("MariaDB belum terbuka di", HOST, PORT)',
            '    print(error)',
            '    raise SystemExit(1) from error',
            '',
            'cur = cnx.cursor()',
            'cur.execute("CREATE DATABASE IF NOT EXISTS stasiun CHARACTER SET utf8mb4")',
            'cur.execute("USE stasiun")',
            'cur.execute(',
            '    """',
            '    CREATE TABLE IF NOT EXISTS telemetry (',
            '        id INT AUTO_INCREMENT PRIMARY KEY,',
            '        received_at VARCHAR(64) NOT NULL,',
            '        device_id VARCHAR(64),',
            '        temperature_c DOUBLE,',
            '        humidity_pct DOUBLE,',
            '        topic VARCHAR(255),',
            '        payload TEXT',
            '    )',
            '    """',
            ')',
            'cur.execute("DELETE FROM telemetry")',
            '',
            'with sqlite3.connect(DB_PATH) as sqlite_db:',
            '    rows = sqlite_db.execute(',
            '        "SELECT received_at, device_id, temperature_c, humidity_pct, topic, payload FROM telemetry ORDER BY id"',
            '    ).fetchall()',
            '',
            'if not rows:',
            '    print("Tabel SQLite masih kosong. Jalankan terima_stasiun.py di FS-40 dulu.")',
            '    raise SystemExit(1)',
            '',
            'cur.executemany(',
            '    """',
            '    INSERT INTO telemetry (received_at, device_id, temperature_c, humidity_pct, topic, payload)',
            '    VALUES (%s, %s, %s, %s, %s, %s)',
            '    """,',
            '    rows,',
            ')',
            'cnx.commit()',
            'print(len(rows), "baris tersalin ke MariaDB.")',
            'if len(rows) >= 10:',
            '    print("10 baris tersalin ke MariaDB.")',
            'cur.close()',
            'cnx.close()',
        ]);
    }

    private function lihat(): string
    {
        return implode("\n", [
            'import mysql.connector',
            '',
            'HOST = "127.0.0.1"',
            'PORT = 3306',
            'USER = "root"',
            'PASSWORD = ""',
            'DATABASE = "stasiun"',
            '',
            'try:',
            '    cnx = mysql.connector.connect(',
            '        host=HOST,',
            '        port=PORT,',
            '        user=USER,',
            '        password=PASSWORD,',
            '        database=DATABASE,',
            '    )',
            'except mysql.connector.Error as error:',
            '    print("MariaDB belum terbuka di", HOST, PORT)',
            '    print(error)',
            '    raise SystemExit(1) from error',
            '',
            'cur = cnx.cursor()',
            'try:',
            '    cur.execute(',
            '        "SELECT id, received_at, device_id, temperature_c, humidity_pct FROM telemetry ORDER BY id"',
            '    )',
            'except mysql.connector.Error as error:',
            '    print("Tabel telemetry belum ada. Jalankan salin_ke_mysql.py dulu.")',
            '    print(error)',
            '    raise SystemExit(1) from error',
            '',
            'rows = cur.fetchall()',
            'print("Jumlah baris:", len(rows))',
            'print("id | received_at | device_id | temperature_c | humidity_pct")',
            'for row in rows[-10:]:',
            '    print(row[0], "|", row[1], "|", row[2], "|", row[3], "|", row[4])',
            'cur.close()',
            'cnx.close()',
        ]);
    }

    private function contoh(): string
    {
        return implode("\n", [
            'from datetime import datetime, timezone',
            '',
            'import mysql.connector',
            '',
            'HOST = "127.0.0.1"',
            'PORT = 3306',
            'USER = "root"',
            'PASSWORD = ""',
            '',
            'try:',
            '    cnx = mysql.connector.connect(host=HOST, port=PORT, user=USER, password=PASSWORD)',
            'except mysql.connector.Error as error:',
            '    print("MariaDB belum terbuka di", HOST, PORT)',
            '    print(error)',
            '    raise SystemExit(1) from error',
            '',
            'cur = cnx.cursor()',
            'cur.execute("CREATE DATABASE IF NOT EXISTS stasiun CHARACTER SET utf8mb4")',
            'cur.execute("USE stasiun")',
            'cur.execute(',
            '    """',
            '    CREATE TABLE IF NOT EXISTS telemetry (',
            '        id INT AUTO_INCREMENT PRIMARY KEY,',
            '        received_at VARCHAR(64) NOT NULL,',
            '        device_id VARCHAR(64),',
            '        temperature_c DOUBLE,',
            '        humidity_pct DOUBLE,',
            '        topic VARCHAR(255),',
            '        payload TEXT',
            '    )',
            '    """',
            ')',
            'cur.execute("DELETE FROM telemetry")',
            'now = datetime.now(timezone.utc).astimezone().isoformat(timespec="seconds")',
            'rows = []',
            'for i in range(10):',
            '    rows.append(',
            '        (',
            '            now,',
            '            "esp32-meja-01",',
            '            round(27.0 + i * 0.4, 1),',
            '            round(60.0 + i, 1),',
            '            "kodingindonesia/fsiot/esp32-meja-01/telemetry",',
            '            "{}",',
            '        )',
            '    )',
            'cur.executemany(',
            '    """',
            '    INSERT INTO telemetry (received_at, device_id, temperature_c, humidity_pct, topic, payload)',
            '    VALUES (%s, %s, %s, %s, %s, %s)',
            '    """,',
            '    rows,',
            ')',
            'cnx.commit()',
            'print("10 baris contoh masuk MariaDB.")',
            'cur.close()',
            'cnx.close()',
        ]);
    }

    private function body(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $salin = htmlspecialchars($this->salin(), ENT_QUOTES, 'UTF-8');
        $lihat = htmlspecialchars($this->lihat(), ENT_QUOTES, 'UTF-8');
        $contoh = htmlspecialchars($this->contoh(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs41-tools-order.png', 'Urutan lima langkah: browser, XAMPP Control Panel, phpMyAdmin, Notepad, PowerShell', '<strong>Urutan meja kerja (lima langkah):</strong> browser → XAMPP Control Panel Start MySQL → phpMyAdmin buat database <code>stasiun</code> → Notepad menulis berkas → PowerShell <code>pip install -r</code> lalu salin 10 baris. Diagram buatan Koding Indonesia (FS-41).');
        $why = $this->figure('fs41-why-mysql.png', 'Perbandingan SQLite satu berkas sebagai jalur utama dan MariaDB sebagai jalur B', '<strong>SQLite tetap jalur utama. MariaDB adalah jalur B.</strong> Capstone tidak mewajibkan MySQL. Diagram buatan Koding Indonesia (FS-41).');
        $download = $this->figure('fs41-download.png', 'Alur kiri ke kanan: browser, apachefriends.org, pemasang Windows 64-bit, Control Panel', '<strong>Unduh dari situs resmi.</strong> Baca dari kiri ke kanan: browser → apachefriends.org → pemasang Windows 64-bit → ikon Control Panel. Diagram buatan Koding Indonesia (FS-41).');
        $xampp = $this->figure('fs41-xampp.png', 'Ilustrasi XAMPP Control Panel dengan Apache dan MySQL Running port 3306', '<strong>MySQL sudah Running.</strong> Port <code>3306</code>. Apache boleh menyala agar phpMyAdmin terbuka. Ilustrasi buatan Koding Indonesia (FS-41), meniru <a href="https://www.apachefriends.org/" target="_blank" rel="noopener noreferrer">XAMPP Control Panel</a> oleh Apache Friends (GPL). Tangkapan layar resmi tidak dipakai utuh.');
        $phpmyadmin = $this->figure('fs41-phpmyadmin.png', 'Ilustrasi phpMyAdmin menampilkan database stasiun tanpa tabel', '<strong>Database stasiun sudah terlihat.</strong> Tabel masih kosong sebelum Python menyalin. Ilustrasi buatan Koding Indonesia (FS-41), meniru phpMyAdmin. Tampilan resmi tidak dipakai utuh.');
        $pip = $this->figure('fs41-pip-venv.png', 'Alur kiri ke kanan: folder fsiot-fs39, venv, pip install -r, mysql-connector-python 26.7.0', '<strong>venv yang sama, tambah satu baris terkunci.</strong> Baca dari kiri ke kanan: folder → venv FS-39 → pip → <code>mysql-connector-python==26.7.0</code>. Diagram buatan Koding Indonesia (FS-41).');
        $copy = $this->figure('fs41-copy-flow.png', 'Alur kiri ke kanan: stasiun.db, script Python, port 3306, tabel telemetry, 10 baris', '<strong>Gambar utama — salin histori, jangan hapus berkas.</strong> Baca dari kiri ke kanan: SQLite → Python → MariaDB → 10 baris. Diagram buatan Koding Indonesia (FS-41).');
        $select = $this->figure('fs41-select.png', 'Keluaran lihat_mysql.py menampilkan jumlah baris 10 dan cuplikan tabel telemetry', '<strong>Bukti berhasil — 10 baris di MariaDB.</strong> Angka suhu boleh berbeda. Yang dikunci adalah jumlah baris. Diagram buatan Koding Indonesia (FS-41).');
        $troubleshooting = $this->figure('fs41-troubleshooting.png', 'Empat pemeriksaan: MySQL Running, sandi root, stasiun.db, port 3306', '<strong>Skema bantu.</strong> Python ke <code>127.0.0.1:3306</code>. Jangan buka port ke internet. Diagram buatan Koding Indonesia (FS-41).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini dan <a href="https://www.apachefriends.org/" target="_blank" rel="noopener noreferrer">apachefriends.org</a>. Jangan ketik pip dulu.'],
            ['title' => 'Buka XAMPP Control Panel', 'text' => 'Kalau belum terpasang, unduh pemasang Windows 64-bit, lalu buka Control Panel dari Start. Tekan Start pada baris <strong>MySQL</strong> sampai tertulis Running.'],
            ['title' => 'Buka phpMyAdmin', 'text' => 'Di browser yang sama, buka <code>http://127.0.0.1/phpmyadmin/</code>. Buat database bernama <code>stasiun</code>. Jangan ketik SQL panjang dulu.'],
            ['title' => 'Buka Notepad, tulis berkas', 'text' => 'Simpan <code>requirements.txt</code>, <code>salin_ke_mysql.py</code>, dan <code>lihat_mysql.py</code> di folder <code>Documents\\fsiot-fs39</code>. All files, bukan <code>.txt</code>.'],
            ['title' => 'Buka PowerShell, pasang pustaka', 'text' => 'Start → ketik PowerShell. Tidak perlu <em>Run as administrator</em>. Tempel <code>pip install -r requirements.txt</code> memakai python di venv, lalu jalankan salinan.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = <code>lihat_mysql.py</code> mencetak <code>Jumlah baris: 10</code>. ESP32 boleh menyala, tetapi tidak wajib.');

        return <<<'HTML'
<h2>Pendahuluan — gudang pindah ke server kecil</h2>
<p><strong>FS-41 / #111 (ini)</strong> adalah lab <strong>opsional</strong>. Kemarin Python menyimpan 10 baris di berkas <code>stasiun.db</code>. Hari ini tugasnya lain: <strong>salin histori itu ke MariaDB di komputer ini</strong>.</p>
<p><strong>Intinya:</strong> pasang XAMPP, nyalakan MariaDB di Control Panel, buat database <code>stasiun</code> di phpMyAdmin, lalu Python menyalin paling sedikit 10 baris. SQLite tidak dihapus.</p>
<p><strong>Analogi:</strong> SQLite adalah buku catatan di laci. MariaDB adalah lemari arsip di ruang server mini. Buku tetap di laci. Hari ini kita menyalin isinya ke lemari, supaya kamu merasakan bedanya berkas versus layanan.</p>
<p>Prasyarat lab: FS-40 (<code>stasiun.db</code> berisi data) dan FS-39 (venv). ESP32 <strong>boleh menyala</strong>, dan <strong>boleh dicabut</strong> — yang disalin adalah berkas SQLite, bukan papan. Tidak ada kabel baru, tidak ada Upload, tidak ada Flask, <strong>Bukan AC 220V</strong>.</p>
<p>Kalau satu PC lab sudah cukup dengan SQLite, kamu <strong>boleh melewati artikel ini</strong>. Capstone dan Hero Complete tidak mewajibkan MySQL. FS-42 tetap membaca SQLite.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>XAMPP Control Panel menampilkan MySQL <strong>Running</strong> di port <code>3306</code>.</li>
<li>phpMyAdmin menampilkan database <code>stasiun</code>.</li>
<li>Folder <code>Documents\fsiot-fs39</code> berisi <code>requirements.txt</code> dengan <code>mysql-connector-python==26.7.0</code>.</li>
<li><code>salin_ke_mysql.py</code> mencetak <code>10 baris tersalin ke MariaDB.</code></li>
<li><code>lihat_mysql.py</code> mencetak <code>Jumlah baris: 10</code>.</li>
<li>Berkas <code>stasiun.db</code> masih ada di folder lab.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> belum Flask, belum REST, belum dashboard, belum user production. Bukti cukup = 10 baris MariaDB. Jalur utama tetap SQLite.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>SQLite</strong> — basis data satu berkas. Hari ini sumbernya <code>stasiun.db</code>.</li>
<li><strong>MariaDB</strong> — layanan basis data yang menjawab di port <code>3306</code>. XAMPP memasangnya; Python memakai konektor MySQL.</li>
<li><strong>MySQL</strong> — nama protokol yang dipakai konektor. Di lab ini artinya percakapan ke MariaDB, bukan merek wajib terpisah.</li>
<li><strong>XAMPP</strong> — kotak Apache + MariaDB + phpMyAdmin dari Apache Friends. Control Panel adalah jendela Start/Stop.</li>
<li><strong>phpMyAdmin</strong> — halaman browser untuk melihat database tanpa mengetik klien <code>mysql</code> dulu.</li>
<li><strong>root</strong> — pengguna lab bawaan XAMPP. Sandi kosong hanya untuk komputer ini, bukan untuk internet.</li>
<li><strong>127.0.0.1:3306</strong> — komputer ini, port MariaDB. Bukan IP ESP32, bukan broker MQTT <code>1883</code>.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> Flask, REST, Arduino IDE, AC 220V, database di awan, membuka port 3306 ke internet, atau mengubah ExecutionPolicy. Node-RED FS-38 boleh tetap terbuka. MQTTX tidak wajib kecuali kamu mengulang FS-40.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa MariaDB, bukan mengganti SQLite</h2>
HTML
            .$why.<<<'HTML'
<p>Satu PC, satu folder lab: SQLite cukup. MariaDB berguna nanti jika banyak program di komputer yang sama ingin membaca histori tanpa membuka berkas. Itu jalur B, bukan ujian naik kelas.</p>
<p>Jangan mencampur: memasang XAMPP <strong>plus</strong> langsung menulis Flask. Satu langkah, satu bukti — hari ini buktinya 10 baris MariaDB, sementara <code>stasiun.db</code> tetap di folder.</p>

<h2>Pasang XAMPP dari apachefriends.org</h2>
HTML
            .$download.<<<'HTML'
<p><strong>Buka browser.</strong> Buka <a href="https://www.apachefriends.org/" target="_blank" rel="noopener noreferrer">apachefriends.org</a>. Pilih unduhan <strong>Windows 64-bit</strong> (bukan Source). Jalankan pemasang. Tomcat, FileZilla, dan Mercury boleh tidak dicentang — lab ini hanya butuh Apache + MySQL + phpMyAdmin.</p>
<p>Kalau ikon <strong>XAMPP Control Panel</strong> sudah ada di Start, <strong>jangan unduh ulang</strong>. Kalau kamu sudah memakai Laragon dari jalur web, Start MariaDB di jendela Laragon — jangan memasang XAMPP kedua. Port <code>3306</code> hanya boleh diisi satu layanan.</p>
<p><strong>macOS atau Linux:</strong> unduh XAMPP untuk sistem itu dari situs yang sama, atau pakai MariaDB yang sudah terpasang. Host tetap <code>127.0.0.1</code>.</p>

<h2>Nyalakan MySQL di Control Panel</h2>
HTML
            .$xampp.<<<'HTML'
<p><strong>Buka dulu XAMPP Control Panel</strong> dari menu Start. Tekan <strong>Start</strong> pada baris MySQL. <strong>Hasil yang dicari:</strong> status Running dan port <code>3306</code>.</p>
<p>Apache boleh Start supaya phpMyAdmin terbuka di browser. Kalau Apache gagal karena port 80 terpakai, MySQL tetap bisa Running — Python tetap menyambung ke <code>3306</code>. phpMyAdmin saja yang menunggu Apache.</p>
<p>Jangan mengubah firewall rumah. Jangan memajukan 3306 ke internet.</p>

<h2>Buka phpMyAdmin, buat database stasiun</h2>
HTML
            .$phpmyadmin.<<<'HTML'
<p><strong>Buka dulu browser.</strong> Ketik alamat ini, lalu Enter:</p>
<pre><code>http://127.0.0.1/phpmyadmin/</code></pre>
<p>Di sisi kiri, pilih <strong>New</strong>. Nama database: <code>stasiun</code>. Collation <code>utf8mb4_general_ci</code> jika terlihat. Tekan Create. <strong>Hasil yang dicari:</strong> nama <code>stasiun</code> muncul di daftar. Tabel masih kosong — itu wajar.</p>
<p>Kalau halaman tidak terbuka: Apache belum Running, atau port 80 dipakai program lain. Python masih bisa membuat database sendiri saat <code>salin_ke_mysql.py</code> jalan; phpMyAdmin adalah cara melihat dengan mata.</p>

<h2>Tambah mysql-connector di requirements.txt</h2>
HTML
            .$pip.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>. Folder <code>.venv</code> dan berkas <code>stasiun.db</code> dari FS-40 harus sudah ada. Jika venv belum ada, ulangi FS-39 sebelum pip. Jika <code>stasiun.db</code> belum ada, ulangi FS-40 atau pakai bonus <code>isi_contoh_mysql.py</code>.</p>
<p><strong>Buka dulu Notepad.</strong> Tempel dua baris ini. File → Save As, All files, nama <code>requirements.txt</code>, folder lab. Baris paho tetap ada supaya venv FS-40 tidak kehilangan kunci:</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p>Jangan <code>pip install mysql-connector-python</code> tanpa angka; besok pustaka bisa berubah. Jangan <code>pip install mysql-connector</code> — itu nama lama yang salah.</p>

<h2>Tulis salin_ke_mysql.py</h2>
HTML
            .$copy.<<<'HTML'
<p><strong>Buka dulu Notepad.</strong> Tempel kode ini. Save As, All files, nama <code>salin_ke_mysql.py</code>, folder <code>Documents\fsiot-fs39</code>.</p>
<pre><code class="language-python">
HTML
            .$salin.<<<'HTML'
</code></pre>
<p>Sandi <code>PASSWORD = ""</code> adalah bawaan lab XAMPP. Kalau PowerShell menulis <code>Access denied</code>, isi sandi yang kamu set saat pemasangan — tetap di komputer ini, jangan diunggah ke internet.</p>
<p>Perintah <code>DELETE FROM telemetry</code> hanya mengosongkan tabel lab sebelum menyalin ulang. Berkas <code>stasiun.db</code> tidak dihapus.</p>

<h2>Jalankan salinan, lalu lihat_mysql.py</h2>
HTML
            .$select.<<<'HTML'
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe -m pip install -r requirements.txt
.\.venv\Scripts\python.exe salin_ke_mysql.py</code></pre>
<p><strong>Hasil yang dicari:</strong> pip menulis <code>Successfully installed</code> atau <code>Already satisfied</code> untuk <code>mysql-connector-python</code>, lalu script menulis <code>10 baris tersalin ke MariaDB.</code></p>
<p>Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>. Perintah di atas sudah memakai <code>python.exe</code> di dalam venv.</p>
<p>Tulis <code>lihat_mysql.py</code> di Notepad, simpan di folder yang sama:</p>
<pre><code class="language-python">
HTML
            .$lihat.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe lihat_mysql.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>Jumlah baris: 10</code> atau lebih. Di phpMyAdmin, buka database <code>stasiun</code>, tabel <code>telemetry</code> — baris yang sama terlihat di browser.</p>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python -m pip install -r requirements.txt</code> dan <code>.venv/bin/python salin_ke_mysql.py</code>.</p>

<h2>Bonus: 10 baris tanpa SQLite</h2>
<p>Tidak wajib. Kalau 10 baris MariaDB sudah terbaca dari salinan SQLite, lab utama selesai. Bonus ini hanya jika <code>stasiun.db</code> belum ada dan kamu tetap ingin melihat SELECT.</p>
<pre><code class="language-python">
HTML
            .$contoh.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe isi_contoh_mysql.py</code></pre>
<p>Ini tidak menggantikan FS-40. FS-42 tetap dirancang membaca SQLite. <strong>Bukan AC 220V.</strong></p>

<h2>Jika baris tidak muncul</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>MySQL belum Running.</strong> Buka dulu XAMPP Control Panel, Start MySQL, tunggu port <code>3306</code>.</li>
<li><strong>Access denied.</strong> Sandi root tidak kosong. Isi <code>PASSWORD</code> di script. Jangan mengganti user Windows.</li>
<li><strong>stasiun.db belum ada.</strong> Ulangi FS-40, atau pakai bonus <code>isi_contoh_mysql.py</code>.</li>
<li><strong>Port 3306 dipakai.</strong> Jangan menjalankan XAMPP dan Laragon bersamaan. Stop salah satu.</li>
<li><strong>pip di luar venv.</strong> Pakai <code>.\.venv\Scripts\python.exe -m pip</code>. Kalau PowerShell menulis <code>No module named mysql</code>, pustaka belum masuk kotak lab.</li>
</ol>

<h2 id="fsiot-mysql-checklist">Checklist sebelum FS-42</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-mysql-checklist-items">
<li>XAMPP Control Panel menampilkan MySQL Running.</li>
<li>phpMyAdmin terbuka di <code>127.0.0.1/phpmyadmin/</code>.</li>
<li>Database <code>stasiun</code> terlihat di phpMyAdmin.</li>
<li><code>requirements.txt</code> mengunci <code>mysql-connector-python==26.7.0</code>.</li>
<li>pip install memakai <code>.venv\Scripts\python.exe -m pip</code>.</li>
<li>Berkas <code>stasiun.db</code> masih ada di folder lab.</li>
<li><code>salin_ke_mysql.py</code> mencetak 10 baris tersalin.</li>
<li><code>lihat_mysql.py</code> menampilkan 10 baris.</li>
<li>Saya tidak mengubah ExecutionPolicy.</li>
<li>Saya tidak menghapus SQLite — MariaDB pelengkap.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: Control Panel → phpMyAdmin → pip → 10 baris MariaDB. Pada FS-42, REST membaca SQLite. Modul ini tetap opsional.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Memasang XAMPP meski Laragon sudah Running.</strong> Satu port 3306, satu layanan.</li>
<li><strong>pip ke Python global.</strong> Pakai python di dalam <code>.venv</code>.</li>
<li><strong>Mengubah ExecutionPolicy.</strong> Tetap pakai <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Menghapus stasiun.db.</strong> SQLite adalah jalur utama FS-42.</li>
<li><strong>Flask hari ini.</strong> Ditunda ke FS-42.</li>
<li><strong>Membuka 3306 ke internet.</strong> Lab hanya <code>127.0.0.1</code>.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa modul ini opsional?</h3>
<p>Satu PC lab sudah cukup dengan SQLite. MariaDB adalah latihan merasakan layanan server. Capstone tidak menilainya.</p>
<h3>MariaDB atau MySQL?</h3>
<p>XAMPP memasang MariaDB. Python memakai <code>mysql-connector-python</code> karena protokolnya sama di port 3306. Kamu tidak perlu memasang MySQL Server terpisah.</p>
<h3>Sandi root kosong aman?</h3>
<p>Hanya di komputer lab yang tidak dibuka ke internet. Jangan memakai pola ini di VPS atau hosting.</p>
<h3>Apache gagal Start?</h3>
<p>Port 80 sering dipakai. MySQL tetap bisa Running. Python tidak membutuhkan Apache. phpMyAdmin yang membutuhkan Apache.</p>
<h3>Apakah Flask hari ini?</h3>
<p>Tidak. Flask satu berkas diajarkan di FS-42, membaca SQLite yang sudah ada sejak FS-40.</p>
<h3>Apakah stasiun.db dihapus?</h3>
<p>Tidak. Salinan ke MariaDB tidak mengganti berkas. FS-42 tetap ke SQLite.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://www.apachefriends.org/" target="_blank" rel="noopener noreferrer">XAMPP — Apache Friends</a> (GPL).</li>
<li><a href="https://mariadb.org/" target="_blank" rel="noopener noreferrer">MariaDB Foundation</a></li>
<li><a href="https://dev.mysql.com/doc/connector-python/en/" target="_blank" rel="noopener noreferrer">MySQL Connector/Python Developer Guide</a></li>
<li><a href="https://pypi.org/project/mysql-connector-python/26.7.0/" target="_blank" rel="noopener noreferrer">mysql-connector-python 26.7.0 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://www.phpmyadmin.net/" target="_blank" rel="noopener noreferrer">phpMyAdmin</a></li>
<li>Diagram urutan tools, SQLite versus MariaDB, unduhan, venv, alur salin, bukti SELECT, dan skema periksa — Koding Indonesia (FS-41).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> histori stasiun sudah terlihat di MariaDB, sementara SQLite tetap di folder. Modul ini opsional. Pada <strong>FS-42</strong>, Flask satu berkas membaca SQLite dan meneruskan perintah MQTT — pelengkap Node-RED, bukan pengganti lab hari ini.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $salin = htmlspecialchars($this->salin(), ENT_QUOTES, 'UTF-8');
        $lihat = htmlspecialchars($this->lihat(), ENT_QUOTES, 'UTF-8');
        $contoh = htmlspecialchars($this->contoh(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs41-tools-order.png', 'Five-step order: browser, XAMPP Control Panel, phpMyAdmin, Notepad, PowerShell', '<strong>Desk order (five steps):</strong> browser → XAMPP Control Panel Start MySQL → phpMyAdmin creates database <code>stasiun</code> → Notepad writes the files → PowerShell <code>pip install -r</code> then copy 10 rows. Diagram by Koding Indonesia (FS-41).');
        $why = $this->figure('fs41-why-mysql.png', 'Comparison of SQLite as the one-file main path and MariaDB as path B', '<strong>SQLite stays the main path. MariaDB is path B.</strong> Capstone does not require MySQL. Diagram by Koding Indonesia (FS-41).');
        $download = $this->figure('fs41-download.png', 'Left-to-right flow: browser, apachefriends.org, Windows 64-bit installer, Control Panel', '<strong>Download from the official site.</strong> Read left to right: browser → apachefriends.org → Windows 64-bit installer → Control Panel icon. Diagram by Koding Indonesia (FS-41).');
        $xampp = $this->figure('fs41-xampp.png', 'XAMPP Control Panel illustration with Apache and MySQL Running on port 3306', '<strong>MySQL is already Running.</strong> Port <code>3306</code>. Apache may be on so phpMyAdmin opens. Illustration by Koding Indonesia (FS-41), modelled on the <a href="https://www.apachefriends.org/" target="_blank" rel="noopener noreferrer">XAMPP Control Panel</a> by Apache Friends (GPL). The official window screenshot is not used as-is.');
        $phpmyadmin = $this->figure('fs41-phpmyadmin.png', 'phpMyAdmin illustration showing the stasiun database with no tables yet', '<strong>The stasiun database is already visible.</strong> The table is still empty until Python copies rows. Illustration by Koding Indonesia (FS-41), modelled on phpMyAdmin. The official window is not used as-is.');
        $pip = $this->figure('fs41-pip-venv.png', 'Left-to-right flow: fsiot-fs39 folder, venv, pip install -r, mysql-connector-python 26.7.0', '<strong>Same venv, one extra pinned line.</strong> Read left to right: folder → FS-39 venv → pip → <code>mysql-connector-python==26.7.0</code>. Diagram by Koding Indonesia (FS-41).');
        $copy = $this->figure('fs41-copy-flow.png', 'Left-to-right flow: stasiun.db, Python script, port 3306, telemetry table, 10 rows', '<strong>Main figure — copy the history, do not delete the file.</strong> Read left to right: SQLite → Python → MariaDB → 10 rows. Diagram by Koding Indonesia (FS-41).');
        $select = $this->figure('fs41-select.png', 'lihat_mysql.py output showing 10 rows and a telemetry table excerpt', '<strong>Proof of success — 10 rows in MariaDB.</strong> Temperature numbers may differ. The lock is the row count. Diagram by Koding Indonesia (FS-41).');
        $troubleshooting = $this->figure('fs41-troubleshooting.png', 'Four checks: MySQL Running, root password, stasiun.db, port 3306', '<strong>Helper schematic.</strong> Python uses <code>127.0.0.1:3306</code>. Do not open the port to the internet. Diagram by Koding Indonesia (FS-41).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide and <a href="https://www.apachefriends.org/" target="_blank" rel="noopener noreferrer">apachefriends.org</a> ready. Do not type pip yet.'],
            ['title' => 'Open XAMPP Control Panel', 'text' => 'If it is not installed, download the Windows 64-bit installer, then open Control Panel from Start. Press Start on the <strong>MySQL</strong> row until it says Running.'],
            ['title' => 'Open phpMyAdmin', 'text' => 'In the same browser, open <code>http://127.0.0.1/phpmyadmin/</code>. Create a database named <code>stasiun</code>. Do not type a long SQL script yet.'],
            ['title' => 'Open Notepad, write the files', 'text' => 'Save <code>requirements.txt</code>, <code>salin_ke_mysql.py</code>, and <code>lihat_mysql.py</code> in <code>Documents\\fsiot-fs39</code>. All files, not <code>.txt</code>.'],
            ['title' => 'Open PowerShell, install the library', 'text' => 'Start → type PowerShell. You do not need <em>Run as administrator</em>. Paste <code>pip install -r requirements.txt</code> using the venv Python, then run the copy script.'],
        ], '<strong>How to test today:</strong> success = <code>lihat_mysql.py</code> prints <code>Jumlah baris: 10</code>. The ESP32 may be on, but it is not required.');

        return <<<'HTML'
<h2>Introduction — the store moves to a small server</h2>
<p><strong>FS-41 / #111 (this article)</strong> is an <strong>optional</strong> lab. Yesterday Python stored 10 rows in the file <code>stasiun.db</code>. Today the job is different: <strong>copy that history into MariaDB on this computer</strong>.</p>
<p><strong>In short:</strong> install XAMPP, start MariaDB in Control Panel, create database <code>stasiun</code> in phpMyAdmin, then Python copies at least 10 rows. SQLite is not deleted.</p>
<p><strong>Analogy:</strong> SQLite is the notebook in the drawer. MariaDB is a filing cabinet in a mini server room. The notebook stays in the drawer. Today we copy its pages into the cabinet so you feel the difference between a file and a service.</p>
<p>Lab prerequisites: FS-40 (<code>stasiun.db</code> with data) and FS-39 (venv). The ESP32 <strong>may stay on</strong>, or it <strong>may be unplugged</strong> — what is copied is the SQLite file, not the board. No new cables, no Upload, no Flask, <strong>Not AC mains</strong>.</p>
<p>If one lab PC is already happy with SQLite, you <strong>may skip this article</strong>. Capstone and Hero Complete do not require MySQL. FS-42 still reads SQLite.</p>

<h2>Expected outcome</h2>
<ul>
<li>XAMPP Control Panel shows MySQL <strong>Running</strong> on port <code>3306</code>.</li>
<li>phpMyAdmin shows the database <code>stasiun</code>.</li>
<li>The folder <code>Documents\fsiot-fs39</code> contains <code>requirements.txt</code> with <code>mysql-connector-python==26.7.0</code>.</li>
<li><code>salin_ke_mysql.py</code> prints <code>10 baris tersalin ke MariaDB.</code></li>
<li><code>lihat_mysql.py</code> prints <code>Jumlah baris: 10</code>.</li>
<li>The file <code>stasiun.db</code> is still in the lab folder.</li>
</ul>
<p><strong>Lab limits today:</strong> no Flask, no REST, no dashboard, no production users. Proof = 10 MariaDB rows. The main path stays SQLite.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>SQLite</strong> — a one-file database. Today the source is <code>stasiun.db</code>.</li>
<li><strong>MariaDB</strong> — a database service that answers on port <code>3306</code>. XAMPP installs it; Python uses the MySQL connector.</li>
<li><strong>MySQL</strong> — the protocol the connector speaks. In this lab it means talking to MariaDB, not a separate brand you must install.</li>
<li><strong>XAMPP</strong> — the Apache + MariaDB + phpMyAdmin box from Apache Friends. Control Panel is the Start/Stop window.</li>
<li><strong>phpMyAdmin</strong> — a browser page for seeing the database without typing the <code>mysql</code> client first.</li>
<li><strong>root</strong> — the default XAMPP lab user. An empty password is only for this computer, not for the internet.</li>
<li><strong>127.0.0.1:3306</strong> — this computer, MariaDB port. Not the ESP32 IP, not the MQTT broker on <code>1883</code>.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> Flask, REST, Arduino IDE, AC mains, a cloud database, opening port 3306 to the internet, or changing ExecutionPolicy. Node-RED from FS-38 may stay open. MQTTX is not required unless you repeat FS-40.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Why MariaDB, instead of replacing SQLite</h2>
HTML
            .$why.<<<'HTML'
<p>One PC, one lab folder: SQLite is enough. MariaDB helps later if several programs on the same computer want to read history without opening a file. That is path B, not a promotion exam.</p>
<p>Do not mix: installing XAMPP <strong>plus</strong> writing Flask at once. One step, one proof — today the proof is 10 MariaDB rows, while <code>stasiun.db</code> stays in the folder.</p>

<h2>Install XAMPP from apachefriends.org</h2>
HTML
            .$download.<<<'HTML'
<p><strong>Open a browser.</strong> Open <a href="https://www.apachefriends.org/" target="_blank" rel="noopener noreferrer">apachefriends.org</a>. Choose the <strong>Windows 64-bit</strong> download (not Source). Run the installer. Tomcat, FileZilla, and Mercury may stay unchecked — this lab only needs Apache + MySQL + phpMyAdmin.</p>
<p>If the <strong>XAMPP Control Panel</strong> icon is already in Start, <strong>do not download again</strong>. If you already use Laragon from the web track, start MariaDB in the Laragon window — do not install a second XAMPP. Port <code>3306</code> may hold only one service.</p>
<p><strong>macOS or Linux:</strong> download XAMPP for that system from the same site, or use MariaDB already installed. The host stays <code>127.0.0.1</code>.</p>

<h2>Start MySQL in Control Panel</h2>
HTML
            .$xampp.<<<'HTML'
<p><strong>Open XAMPP Control Panel first</strong> from the Start menu. Press <strong>Start</strong> on the MySQL row. <strong>What to look for:</strong> status Running and port <code>3306</code>.</p>
<p>Apache may Start so phpMyAdmin opens in the browser. If Apache fails because port 80 is busy, MySQL can still be Running — Python still connects to <code>3306</code>. Only phpMyAdmin waits for Apache.</p>
<p>Do not change the home firewall. Do not expose 3306 to the internet.</p>

<h2>Open phpMyAdmin, create the stasiun database</h2>
HTML
            .$phpmyadmin.<<<'HTML'
<p><strong>Open a browser first.</strong> Type this address, then Enter:</p>
<pre><code>http://127.0.0.1/phpmyadmin/</code></pre>
<p>On the left, choose <strong>New</strong>. Database name: <code>stasiun</code>. Collation <code>utf8mb4_general_ci</code> if you see it. Press Create. <strong>What to look for:</strong> the name <code>stasiun</code> appears in the list. The table is still empty — that is expected.</p>
<p>If the page does not open: Apache is not Running, or port 80 is used by another program. Python can still create the database when <code>salin_ke_mysql.py</code> runs; phpMyAdmin is the way to see it with your eyes.</p>

<h2>Add mysql-connector in requirements.txt</h2>
HTML
            .$pip.<<<'HTML'
<p><strong>Open File Explorer first</strong>, go to <code>Documents\fsiot-fs39</code>. The <code>.venv</code> folder and the file <code>stasiun.db</code> from FS-40 must already be there. If the venv is missing, repeat FS-39 before pip. If <code>stasiun.db</code> is missing, repeat FS-40 or use the bonus <code>isi_contoh_mysql.py</code>.</p>
<p><strong>Open Notepad first.</strong> Paste these two lines. File → Save As, All files, name <code>requirements.txt</code>, lab folder. Keep the paho line so the FS-40 venv does not lose its pin:</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p>Do not run <code>pip install mysql-connector-python</code> without a number; the library can change tomorrow. Do not run <code>pip install mysql-connector</code> — that is the wrong old name.</p>

<h2>Write salin_ke_mysql.py</h2>
HTML
            .$copy.<<<'HTML'
<p><strong>Open Notepad first.</strong> Paste this code. Save As, All files, name <code>salin_ke_mysql.py</code>, folder <code>Documents\fsiot-fs39</code>.</p>
<pre><code class="language-python">
HTML
            .$salin.<<<'HTML'
</code></pre>
<p>The password <code>PASSWORD = ""</code> is the default XAMPP lab value. If PowerShell writes <code>Access denied</code>, fill in the password you set during setup — keep it on this computer, do not upload it to the internet.</p>
<p>The <code>DELETE FROM telemetry</code> command only clears the lab table before copying again. The file <code>stasiun.db</code> is not deleted.</p>

<h2>Run the copy, then lihat_mysql.py</h2>
HTML
            .$select.<<<'HTML'
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. When the text appears, press Enter.</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe -m pip install -r requirements.txt
.\.venv\Scripts\python.exe salin_ke_mysql.py</code></pre>
<p><strong>What to look for:</strong> pip writes <code>Successfully installed</code> or <code>Already satisfied</code> for <code>mysql-connector-python</code>, then the script writes <code>10 baris tersalin ke MariaDB.</code></p>
<p>If <code>.\.venv\Scripts\Activate.ps1</code> is rejected, <strong>do not change ExecutionPolicy</strong>. The command above already uses <code>python.exe</code> inside the venv.</p>
<p>Write <code>lihat_mysql.py</code> in Notepad and save it in the same folder:</p>
<pre><code class="language-python">
HTML
            .$lihat.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe lihat_mysql.py</code></pre>
<p><strong>What to look for:</strong> <code>Jumlah baris: 10</code> or more. In phpMyAdmin, open database <code>stasiun</code>, table <code>telemetry</code> — the same rows appear in the browser.</p>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python -m pip install -r requirements.txt</code> and <code>.venv/bin/python salin_ke_mysql.py</code>.</p>

<h2>Bonus: 10 rows without SQLite</h2>
<p>Not required. If 10 MariaDB rows already read from the SQLite copy, the main lab is done. This bonus is only if <code>stasiun.db</code> is missing and you still want to see SELECT.</p>
<pre><code class="language-python">
HTML
            .$contoh.<<<'HTML'
</code></pre>
<pre><code>.\.venv\Scripts\python.exe isi_contoh_mysql.py</code></pre>
<p>This does not replace FS-40. FS-42 is still designed to read SQLite. <strong>Not AC mains.</strong></p>

<h2>If rows do not appear</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>MySQL is not Running.</strong> Open XAMPP Control Panel first, Start MySQL, wait for port <code>3306</code>.</li>
<li><strong>Access denied.</strong> The root password is not empty. Fill <code>PASSWORD</code> in the script. Do not change the Windows user.</li>
<li><strong>stasiun.db is missing.</strong> Repeat FS-40, or use the bonus <code>isi_contoh_mysql.py</code>.</li>
<li><strong>Port 3306 is in use.</strong> Do not run XAMPP and Laragon at the same time. Stop one of them.</li>
<li><strong>pip outside the venv.</strong> Use <code>.\.venv\Scripts\python.exe -m pip</code>. If PowerShell writes <code>No module named mysql</code>, the library is not in the lab box.</li>
</ol>

<h2 id="fsiot-mysql-checklist">Checklist before FS-42</h2>
<p>Tick an item only after you have actually done it. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-mysql-checklist-items">
<li>XAMPP Control Panel shows MySQL Running.</li>
<li>phpMyAdmin opens at <code>127.0.0.1/phpmyadmin/</code>.</li>
<li>The database <code>stasiun</code> is visible in phpMyAdmin.</li>
<li><code>requirements.txt</code> pins <code>mysql-connector-python==26.7.0</code>.</li>
<li>pip install uses <code>.venv\Scripts\python.exe -m pip</code>.</li>
<li>The file <code>stasiun.db</code> is still in the lab folder.</li>
<li><code>salin_ke_mysql.py</code> prints that 10 rows were copied.</li>
<li><code>lihat_mysql.py</code> shows 10 rows.</li>
<li>I did not change ExecutionPolicy.</li>
<li>I did not delete SQLite — MariaDB complements it.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: Control Panel → phpMyAdmin → pip → 10 MariaDB rows. In FS-42, REST reads SQLite. This module stays optional.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Installing XAMPP while Laragon is already Running.</strong> One port 3306, one service.</li>
<li><strong>pip into global Python.</strong> Use the Python inside <code>.venv</code>.</li>
<li><strong>Changing ExecutionPolicy.</strong> Keep using <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Deleting stasiun.db.</strong> SQLite is the main path for FS-42.</li>
<li><strong>Flask today.</strong> That waits for FS-42.</li>
<li><strong>Opening 3306 to the internet.</strong> The lab is only <code>127.0.0.1</code>.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why is this module optional?</h3>
<p>One lab PC is already enough with SQLite. MariaDB is practice feeling a server service. Capstone does not grade it.</p>
<h3>MariaDB or MySQL?</h3>
<p>XAMPP installs MariaDB. Python uses <code>mysql-connector-python</code> because the protocol is the same on port 3306. You do not need a separate MySQL Server.</p>
<h3>Is an empty root password safe?</h3>
<p>Only on a lab computer that is not opened to the internet. Do not use this pattern on a VPS or host.</p>
<h3>Apache fails to Start?</h3>
<p>Port 80 is often busy. MySQL can still be Running. Python does not need Apache. phpMyAdmin does.</p>
<h3>Is Flask today?</h3>
<p>No. One-file Flask is taught in FS-42, reading the SQLite file that has existed since FS-40.</p>
<h3>Is stasiun.db deleted?</h3>
<p>No. Copying to MariaDB does not replace the file. FS-42 still uses SQLite.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://www.apachefriends.org/" target="_blank" rel="noopener noreferrer">XAMPP — Apache Friends</a> (GPL).</li>
<li><a href="https://mariadb.org/" target="_blank" rel="noopener noreferrer">MariaDB Foundation</a></li>
<li><a href="https://dev.mysql.com/doc/connector-python/en/" target="_blank" rel="noopener noreferrer">MySQL Connector/Python Developer Guide</a></li>
<li><a href="https://pypi.org/project/mysql-connector-python/26.7.0/" target="_blank" rel="noopener noreferrer">mysql-connector-python 26.7.0 on PyPI</a></li>
<li><a href="https://docs.python.org/3/library/sqlite3.html" target="_blank" rel="noopener noreferrer">sqlite3 — Python docs</a></li>
<li><a href="https://www.phpmyadmin.net/" target="_blank" rel="noopener noreferrer">phpMyAdmin</a></li>
<li>Diagrams for tool order, SQLite versus MariaDB, the download, the venv, the copy flow, the SELECT proof, and the check schematic — Koding Indonesia (FS-41).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> station history is visible in MariaDB, while SQLite stays in the folder. This module is optional. In <strong>FS-42</strong>, one-file Flask reads SQLite and forwards MQTT commands — a complement to Node-RED, not a replacement for today’s lab.</p>
HTML;
    }
}
