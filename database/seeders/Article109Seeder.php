<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article109Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-python-dari-nol-script-pertama';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'python', 'esp32'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Pasang Python di PC lalu jalankan script siap terima data stasiun',
            'title_en' => 'Install Python on the PC then run the station-ready script',
            'excerpt' => 'FS-39 / #109: Python 3.11+ terpasang dari python.org, PATH dicentang, venv dibuat, script siap_stasiun.py mencetak Siap terima data stasiun. Belum MQTT.',
            'excerpt_en' => 'FS-39 / #109: Python 3.11+ is installed from python.org, PATH is checked, a venv is created, and siap_stasiun.py prints Siap terima data stasiun. No MQTT yet.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Pasang Python di PC lalu jalankan script siap stasiun — FS-39',
            'seo_title_en' => 'Install Python on the PC and Run the Station-Ready Script — FS-39',
            'seo_description' => 'Lab pemula: unduh Python 3.11+ dari python.org, centang PATH, buat venv, jalankan script pertama. Belum paho-mqtt dan belum SQLite.',
            'seo_description_en' => 'A first lab: download Python 3.11+ from python.org, tick PATH, create a venv, run the first script. No paho-mqtt and no SQLite yet.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'esp32'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs39-cover-python.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs39-cover-python.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs39-cover-python.webp',
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

    private function script(): string
    {
        return implode("\n", [
            'import sys',
            '',
            'print("Siap terima data stasiun")',
            '',
            'if len(sys.argv) > 1:',
            '    nama = sys.argv[1]',
            'else:',
            '    nama = "stasiun-meja-01"',
            '',
            'print("Nama stasiun:", nama)',
        ]);
    }

    private function body(): string
    {
        $script = htmlspecialchars($this->script(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs39-tools-order.png', 'Urutan lima langkah: browser, python.org, centang PATH, PowerShell, lalu Notepad', '<strong>Urutan meja kerja (lima langkah):</strong> browser → python.org → centang Add python.exe to PATH → PowerShell cek versi → Notepad menulis <code>siap_stasiun.py</code>. Jangan <code>pip install paho-mqtt</code> hari ini. Diagram buatan Koding Indonesia (FS-39).');
        $why = $this->figure('fs39-why-pc.png', 'Perbandingan: bukan paho-mqtt hari ini versus python version, venv, dan script hello', '<strong>Python hidup di PC.</strong> ESP32 tetap perangkat. Hari ini tidak ada sketch baru. Diagram buatan Koding Indonesia (FS-39).');
        $download = $this->figure('fs39-download.png', 'Ilustrasi python.org menampilkan tombol unduhan Windows installer', '<strong>python.org sudah menampilkan tombol unduhan Windows.</strong> Buka browser dulu. Jangan Microsoft Store. Ilustrasi buatan Koding Indonesia (FS-39), meniru halaman <a href="https://www.python.org/downloads/" target="_blank" rel="noopener noreferrer">python.org/downloads</a> (Python Software Foundation). Tangkapan layar resmi tidak dipakai utuh.');
        $path = $this->figure('fs39-installer-path.png', 'Wizard pemasang Python dengan kotak Add python.exe to PATH sudah dicentang', '<strong>Centang PATH sebelum Next.</strong> Tanpa kotak itu, PowerShell tidak mengenal perintah <code>python</code>. Tutup PowerShell lama setelah Finish. Diagram buatan Koding Indonesia (FS-39).');
        $version = $this->figure('fs39-version-ok.png', 'Ilustrasi PowerShell menampilkan Python 3.12 dan pip 24', '<strong>PowerShell sudah menampilkan versi Python dan pip.</strong> Angka patch boleh berbeda, asal 3.11 atau lebih baru. Ilustrasi buatan Koding Indonesia (FS-39), meniru jendela PowerShell. Bukan screenshot jendela resmi.');
        $venv = $this->figure('fs39-venv.png', 'Alur kiri ke kanan: folder fsiot-fs39, venv, python.exe di Scripts, lalu script', '<strong>Gambar utama — venv.</strong> Baca dari kiri ke kanan: folder → venv → python di dalamnya → script. Diagram buatan Koding Indonesia (FS-39).');
        $run = $this->figure('fs39-script-run.png', 'Script siap_stasiun.py di kiri dan keluaran Siap terima data stasiun di PowerShell', '<strong>Bukti berhasil.</strong> Notepad menulis berkas <code>.py</code>, PowerShell mencetak <code>Siap terima data stasiun</code>. Diagram buatan Koding Indonesia (FS-39).');
        $argv = $this->figure('fs39-argv.png', 'Perbandingan menjalankan script tanpa argumen dan dengan nama esp32-meja-01', '<strong>Argumen = nama stasiun.</strong> Spasi setelah <code>.py</code> memisahkan perintah dan nama. Diagram buatan Koding Indonesia (FS-39).');
        $troubleshooting = $this->figure('fs39-troubleshooting.png', 'Empat pemeriksaan: Microsoft Store, PATH, PowerShell lama, dan pip yang salah', '<strong>Skema bantu.</strong> Jika jendela Microsoft Store terbuka, itu pemasang yang salah — tutup, unduh dari python.org. Diagram buatan Koding Indonesia (FS-39).');
        $install = $this->stepsCard([
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini. Jangan buka Microsoft Store. Jangan ketik perintah Python dulu.'],
            ['title' => 'Unduh dari python.org', 'text' => 'Buka <a href="https://www.python.org/downloads/" target="_blank" rel="noopener noreferrer">python.org/downloads</a>. Tombol besar Windows installer, versi <strong>3.11 atau lebih baru</strong>. Jalankan berkas <code>.exe</code>.'],
            ['title' => 'Centang PATH, selesaikan wizard', 'text' => 'Di halaman pertama pemasang, centang <strong>Add python.exe to PATH</strong>. Baru klik Install Now atau Next sampai Finish. Tutup PowerShell yang lama.'],
            ['title' => 'Buka PowerShell, cek versi', 'text' => 'Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>. Tempel <code>python --version</code>, lalu <code>python -m pip --version</code>.'],
            ['title' => 'Buka Notepad, tulis script', 'text' => 'Simpan sebagai <code>siap_stasiun.py</code> di folder lab. Di PowerShell, jalankan berkas itu sampai muncul <code>Siap terima data stasiun</code>.'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = PowerShell menampilkan versi 3.11 atau lebih baru, lalu script mencetak <code>Siap terima data stasiun</code>. Belum perlu ESP32.');

        return <<<'HTML'
<h2>Pendahuluan — Python masuk ke meja PC</h2>
<p><strong>FS-39 / #109 (ini)</strong> membuka fase FULLSTACK. Kemarin Node-RED memegang ambang. Hari ini tugasnya lain: <strong>PC harus bisa menjalankan program penerima data</strong>.</p>
<p><strong>Intinya:</strong> pasang Python 3.11 atau lebih baru dari python.org, centang PATH, buat venv, jalankan <code>siap_stasiun.py</code>. Keluaran yang dicari: <code>Siap terima data stasiun</code>.</p>
<p><strong>Analogi:</strong> ESP32 adalah pekerja di gudang. Python adalah bengkel di kantor. Hari ini kita merakit bengkelnya. Radio MQTT belum dipasang — itu FS-40.</p>
<p>Prasyarat lab: fase CONNECTED sudah dilalui, termasuk Node-RED di FS-38. ESP32 <strong>boleh dicabut</strong> hari ini. Tidak ada kabel baru, tidak ada Upload, tidak ada paho-mqtt.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>PowerShell menampilkan <code>python --version</code> berisi <code>3.11</code> atau lebih baru.</li>
<li><code>python -m pip --version</code> menampilkan baris pip, bukan jendela Store.</li>
<li>Folder <code>Documents\fsiot-fs39</code> berisi <code>.venv</code> dan <code>siap_stasiun.py</code>.</li>
<li>Perintah <code>python siap_stasiun.py</code> mencetak <code>Siap terima data stasiun</code>.</li>
<li>Perintah dengan argumen mencetak nama stasiun yang kamu ketik.</li>
</ul>
<p><strong>Batas lab hari ini:</strong> tidak ada MQTT, paho-mqtt, SQLite, Flask, atau Upload ESP32. Bukti cukup = versi + script + argumen.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>Pemasang</strong> — berkas <code>.exe</code> dari python.org yang menaruh Python di Windows.</li>
<li><strong>PATH</strong> — daftar folder yang dicari PowerShell saat kamu mengetik <code>python</code>. Wajib dicentang di wizard.</li>
<li><strong>pip</strong> — alat pengunduh pustaka. Hari ini hanya dicek versinya, tidak dipakai memasang apa pun.</li>
<li><strong>venv</strong> — kotak Python khusus folder lab. Isinya tidak campur dengan proyek lain.</li>
<li><strong>Argumen</strong> — teks di belakang nama script, misalnya nama stasiun.</li>
<li><strong>Microsoft Store</strong> — jalur Windows yang sering membuka jendela aplikasi, bukan pemasang python.org. Jangan dipakai hari ini.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Jangan dipakai hari ini:</strong> Microsoft Store, <code>pip install paho-mqtt</code>, SQLite, Laragon, <code>php artisan</code>, Arduino IDE, atau colok kabel baru. Node-RED FS-38 boleh tetap terbuka, tetapi tidak diubah.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa Python di PC, bukan di ESP32</h2>
HTML
            .$why.<<<'HTML'
<p>ESP32 sudah tahu mengirim suhu dan mematuhi perintah. Yang belum ada di meja adalah program PC yang kelak menerima data itu. Python adalah bahasa yang dipakai jalur FULLSTACK mulai hari ini.</p>
<p>Jangan mencampur: memasang Python <strong>plus</strong> langsung berlangganan MQTT. Satu langkah, satu bukti — hari ini buktinya versi dan script.</p>

<h2>Unduh pemasang dari python.org</h2>
HTML
            .$download.<<<'HTML'
<p><strong>Buka dulu browser</strong> ke <a href="https://www.python.org/downloads/" target="_blank" rel="noopener noreferrer">python.org/downloads</a>. Klik tombol besar untuk Windows. Versi patch (angka terakhir) boleh berbeda. Yang dikunci: <strong>3.11 atau lebih baru</strong>.</p>
<p>Simpan berkas <code>.exe</code>, lalu double-klik. Jika Windows menampilkan SmartScreen, pilih <em>More info</em> lalu <em>Run anyway</em> hanya untuk pemasang python.org yang baru kamu unduh.</p>
<p><strong>macOS:</strong> buka browser ke halaman yang sama, unduh pemasang macOS, lalu buka <strong>Terminal</strong> untuk <code>python3 --version</code>. <strong>Ubuntu atau Debian:</strong> buka <strong>Terminal</strong> dulu; banyak instalasi sudah punya <code>python3</code>. Jika belum, ikuti <a href="https://docs.python.org/3/using/unix.html" target="_blank" rel="noopener noreferrer">dokumentasi Unix Python</a>. Jangan memakai Microsoft Store sebagai pengganti di Windows.</p>

<h2>Centang PATH, selesaikan wizard</h2>
HTML
            .$path.<<<'HTML'
<p>Halaman pertama wizard menampilkan kotak kecil di bawah: <strong>Add python.exe to PATH</strong>. Centang itu <strong>sebelum</strong> Install Now. Terima pengaturan bawaan. Klik Next sampai Finish.</p>
<p>Setelah Finish, <strong>tutup semua jendela PowerShell yang lama</strong>. PATH baru hanya dibaca jendela yang baru dibuka.</p>

<h2>Buka PowerShell, cek python dan pip</h2>
HTML
            .$version.<<<'HTML'
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<pre><code>python --version
python -m pip --version</code></pre>
<p><strong>Hasil yang dicari:</strong> baris <code>Python 3.12.x</code> atau 3.11/3.13, lalu baris <code>pip</code>. Angka persismu boleh berbeda.</p>
<p>Jika perintah <code>python</code> membuka Microsoft Store, atau PowerShell menulis tidak dikenali: jangan memasang dari Store. Pakai cadangan Windows:</p>
<pre><code>py --version
py -m pip --version</code></pre>
<p>Kalau <code>py</code> jalan tetapi <code>python</code> tidak, pemasang python.org perlu diulang dengan PATH tercentang, lalu PowerShell baru. Rujukan: <a href="https://docs.python.org/3/using/windows.html" target="_blank" rel="noopener noreferrer">Using Python on Windows</a>.</p>

<h2>Buat folder lab dan venv</h2>
HTML
            .$venv.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong>, masuk ke <strong>Documents</strong>, buat folder <code>fsiot-fs39</code>.</p>
<p>Di PowerShell:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
python -m venv .venv
.\.venv\Scripts\python.exe --version</code></pre>
<p><strong>Hasil yang dicari:</strong> folder <code>.venv</code> muncul di File Explorer, dan perintah terakhir mengulang versi Python.</p>
<p>Mengaktifkan venv bersifat opsional hari ini. Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>. Tetap pakai <code>.\.venv\Scripts\python.exe</code> untuk menjalankan script. Rujukan: <a href="https://docs.python.org/3/tutorial/venv.html" target="_blank" rel="noopener noreferrer">venv — Python tutorial</a>.</p>

<h2>Tulis script di Notepad</h2>
HTML
            .$run.<<<'HTML'
<p><strong>Buka dulu Notepad.</strong> Start → ketik <strong>Notepad</strong>. Jangan VS Code dulu jika belum terpasang — editor teks bawaan cukup.</p>
<p>Tempel kode ini. Lalu <strong>File → Save As</strong>. Di <em>Save as type</em> pilih <em>All files</em>. Nama berkas: <code>siap_stasiun.py</code>. Folder: <code>Documents\fsiot-fs39</code>. Jangan biarkan Windows menambah <code>.txt</code>.</p>
<pre><code class="language-python">
HTML
            .$script.<<<'HTML'
</code></pre>
<p>Baris <code>import sys</code> memakai pustaka bawaan. Tidak ada <code>pip install</code>.</p>

<h2>Jalankan script, lalu dengan argumen</h2>
HTML
            .$argv.<<<'HTML'
<p>Pastikan PowerShell masih di folder lab:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe siap_stasiun.py
.\.venv\Scripts\python.exe siap_stasiun.py esp32-meja-01</code></pre>
<p><strong>Hasil yang dicari:</strong> baris pertama selalu <code>Siap terima data stasiun</code>. Tanpa argumen, nama menjadi <code>stasiun-meja-01</code>. Dengan argumen, nama menjadi <code>esp32-meja-01</code>.</p>
<p>Kalau PowerShell menulis tidak dapat menemukan berkas: cek File Explorer — nama harus <code>siap_stasiun.py</code>, bukan <code>siap_stasiun.py.txt</code>.</p>

<h2>Jika python tidak dikenali</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Jendela Microsoft Store terbuka.</strong> Tutup. Itu bukan pemasang python.org. Unduh ulang dari python.org, centang PATH.</li>
<li><strong>Lupa centang PATH.</strong> Jalankan pemasang lagi, pilih Modify atau uninstall lalu pasang ulang, centang <strong>Add python.exe to PATH</strong>.</li>
<li><strong>PowerShell lama.</strong> Tutup semua, buka baru, ulangi <code>python --version</code>.</li>
<li><strong>Perintah <code>pip</code> saja gagal.</strong> Pakai <code>python -m pip --version</code> supaya pip yang dipakai adalah milik Python yang sama.</li>
</ol>

<h2 id="fsiot-python-checklist">Checklist sebelum FS-40</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-python-checklist-items">
<li>Browser membuka python.org, bukan Microsoft Store.</li>
<li>Kotak Add python.exe to PATH tercentang sebelum Next.</li>
<li>PowerShell baru menampilkan <code>python --version</code> 3.11 atau lebih baru.</li>
<li><code>python -m pip --version</code> menampilkan baris pip.</li>
<li>Folder <code>Documents\fsiot-fs39</code> sudah dibuat.</li>
<li><code>python -m venv .venv</code> membuat folder <code>.venv</code>.</li>
<li>Berkas <code>siap_stasiun.py</code> tersimpan, bukan <code>.txt</code>.</li>
<li>Script mencetak <code>Siap terima data stasiun</code>.</li>
<li>Argumen <code>esp32-meja-01</code> tercetak sebagai nama stasiun.</li>
<li>Saya tidak menjalankan <code>pip install paho-mqtt</code> hari ini.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: unduh → PATH → versi → venv → script. Pada FS-40, script yang sama folder ini akan berlangganan MQTT.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Memasang dari Microsoft Store.</strong> Tutup, ulangi dari python.org.</li>
<li><strong>Melewatkan centang PATH.</strong> <code>python</code> tidak dikenali.</li>
<li><strong>Berkas <code>.py.txt</code>.</strong> Di Save As pilih All files.</li>
<li><strong><code>pip install paho-mqtt</code> hari ini.</strong> Ditunda ke FS-40.</li>
<li><strong>Mengubah ExecutionPolicy karena Activate.ps1 ditolak.</strong> Pakai <code>.venv\Scripts\python.exe</code> langsung.</li>
<li><strong>Membuka Arduino IDE untuk lab ini.</strong> ESP32 boleh dicabut.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa tidak langsung MQTT?</h3>
<p>Kalau PATH rusak, subscriber MQTT akan gagal dengan pesan yang membingungkan. Hari ini kita pastikan Python jalan.</p>
<h3>Kenapa venv, bukan Python global saja?</h3>
<p>venv menahan pustaka lab ini di satu folder. FS-40 akan mengunci versi di situ.</p>
<h3>Python 3.13 boleh?</h3>
<p>Boleh, asal 3.11 atau lebih baru. Jangan 2.7.</p>
<h3>Bolehkah VS Code?</h3>
<p>Boleh setelah script jalan di Notepad + PowerShell. Editor teks diajarkan lebih dalam di modul kemudian.</p>
<h3>Apakah Node-RED diganti?</h3>
<p>Tidak. Node-RED tetap otak aturan visual. Python pelengkap penerima data.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://www.python.org/downloads/" target="_blank" rel="noopener noreferrer">Python Downloads</a>. Python Software Foundation. Python adalah merek Python Software Foundation.</li>
<li><a href="https://docs.python.org/3/using/windows.html" target="_blank" rel="noopener noreferrer">Using Python on Windows</a></li>
<li><a href="https://docs.python.org/3/tutorial/venv.html" target="_blank" rel="noopener noreferrer">venv — Creation of virtual environments</a></li>
<li><a href="https://docs.python.org/3/using/unix.html" target="_blank" rel="noopener noreferrer">Using Python on Unix</a></li>
<li><a href="https://docs.python.org/3/library/sys.html#sys.argv" target="_blank" rel="noopener noreferrer">sys.argv</a></li>
<li>Diagram urutan tools, Python di PC, kotak PATH, venv, script, argumen, dan skema periksa — Koding Indonesia (FS-39).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> Python sudah jalan di PC. Pada <strong>FS-40</strong>, script di folder yang sama berlangganan MQTT dan menyimpan baris ke SQLite — pelengkap Node-RED, bukan pengganti lab hari ini.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $script = htmlspecialchars($this->script(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs39-tools-order.png', 'Five-step order: browser, python.org, tick PATH, PowerShell, then Notepad', '<strong>Desk order (five steps):</strong> browser → python.org → tick Add python.exe to PATH → PowerShell version check → Notepad writes <code>siap_stasiun.py</code>. Do not <code>pip install paho-mqtt</code> today. Diagram by Koding Indonesia (FS-39).');
        $why = $this->figure('fs39-why-pc.png', 'Comparison: not paho-mqtt today versus python version, venv, and a hello script', '<strong>Python lives on the PC.</strong> The ESP32 stays the device. No new sketch today. Diagram by Koding Indonesia (FS-39).');
        $download = $this->figure('fs39-download.png', 'Illustration of python.org showing the Windows installer download button', '<strong>python.org is already showing the Windows download button.</strong> Open a browser first. Do not use the Microsoft Store. Illustration by Koding Indonesia (FS-39), modelled on <a href="https://www.python.org/downloads/" target="_blank" rel="noopener noreferrer">python.org/downloads</a> (Python Software Foundation). The official page screenshot is not used as-is.');
        $path = $this->figure('fs39-installer-path.png', 'Python installer wizard with Add python.exe to PATH already ticked', '<strong>Tick PATH before Next.</strong> Without that box, PowerShell does not know the <code>python</code> command. Close old PowerShell after Finish. Diagram by Koding Indonesia (FS-39).');
        $version = $this->figure('fs39-version-ok.png', 'PowerShell illustration showing Python 3.12 and pip 24', '<strong>PowerShell is already showing the Python and pip versions.</strong> The patch number may differ, as long as it is 3.11 or newer. Illustration by Koding Indonesia (FS-39), modelled on a PowerShell window. The official window screenshot is not used.');
        $venv = $this->figure('fs39-venv.png', 'Left-to-right flow: fsiot-fs39 folder, venv, python.exe in Scripts, then the script', '<strong>Main figure — venv.</strong> Read left to right: folder → venv → python inside it → script. Diagram by Koding Indonesia (FS-39).');
        $run = $this->figure('fs39-script-run.png', 'siap_stasiun.py on the left and Siap terima data stasiun output in PowerShell', '<strong>Proof of success.</strong> Notepad writes a <code>.py</code> file, PowerShell prints <code>Siap terima data stasiun</code>. Diagram by Koding Indonesia (FS-39).');
        $argv = $this->figure('fs39-argv.png', 'Comparison of running the script with no argument and with the name esp32-meja-01', '<strong>The argument is the station name.</strong> The space after <code>.py</code> separates the command from the name. Diagram by Koding Indonesia (FS-39).');
        $troubleshooting = $this->figure('fs39-troubleshooting.png', 'Four checks: Microsoft Store, PATH, old PowerShell, and the wrong pip', '<strong>Helper schematic.</strong> If a Microsoft Store window opens, that is the wrong installer — close it and download from python.org. Diagram by Koding Indonesia (FS-39).');
        $install = $this->stepsCard([
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this guide ready. Do not open the Microsoft Store. Do not type Python commands yet.'],
            ['title' => 'Download from python.org', 'text' => 'Open <a href="https://www.python.org/downloads/" target="_blank" rel="noopener noreferrer">python.org/downloads</a>. The large Windows installer button, version <strong>3.11 or newer</strong>. Run the <code>.exe</code>.'],
            ['title' => 'Tick PATH, finish the wizard', 'text' => 'On the first installer page, tick <strong>Add python.exe to PATH</strong>. Only then click Install Now or Next until Finish. Close any old PowerShell window.'],
            ['title' => 'Open PowerShell, check versions', 'text' => 'Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>. Paste <code>python --version</code>, then <code>python -m pip --version</code>.'],
            ['title' => 'Open Notepad, write the script', 'text' => 'Save as <code>siap_stasiun.py</code> in the lab folder. In PowerShell, run that file until <code>Siap terima data stasiun</code> appears.'],
        ], '<strong>How to test today:</strong> success = PowerShell shows version 3.11 or newer, then the script prints <code>Siap terima data stasiun</code>. The ESP32 is not needed yet.');

        return <<<'HTML'
<h2>Introduction — Python arrives on the PC desk</h2>
<p><strong>FS-39 / #109 (this article)</strong> opens the FULLSTACK phase. Yesterday Node-RED held the threshold. Today the job is different: <strong>the PC must be able to run a data-receiver program</strong>.</p>
<p><strong>In short:</strong> install Python 3.11 or newer from python.org, tick PATH, create a venv, run <code>siap_stasiun.py</code>. The line to look for: <code>Siap terima data stasiun</code>.</p>
<p><strong>Analogy:</strong> the ESP32 is the warehouse worker. Python is the workshop in the office. Today we assemble the workshop. The MQTT radio is not fitted yet — that is FS-40.</p>
<p>Lab prerequisites: the CONNECTED phase is done, including Node-RED in FS-38. The ESP32 <strong>may be unplugged</strong> today. No new cables, no Upload, no paho-mqtt.</p>

<h2>Expected outcome</h2>
<ul>
<li>PowerShell shows <code>python --version</code> containing <code>3.11</code> or newer.</li>
<li><code>python -m pip --version</code> shows a pip line, not a Store window.</li>
<li>The folder <code>Documents\fsiot-fs39</code> contains <code>.venv</code> and <code>siap_stasiun.py</code>.</li>
<li>The command <code>python siap_stasiun.py</code> prints <code>Siap terima data stasiun</code>.</li>
<li>The command with an argument prints the station name you typed.</li>
</ul>
<p><strong>Lab limits today:</strong> no MQTT, paho-mqtt, SQLite, Flask, or ESP32 Upload. Proof = version + script + argument.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>Installer</strong> — the <code>.exe</code> from python.org that places Python on Windows.</li>
<li><strong>PATH</strong> — the folder list PowerShell searches when you type <code>python</code>. It must be ticked in the wizard.</li>
<li><strong>pip</strong> — the library downloader. Today you only check its version; you do not install anything with it.</li>
<li><strong>venv</strong> — a Python box just for this lab folder. Its contents stay out of other projects.</li>
<li><strong>Argument</strong> — text after the script name, for example a station name.</li>
<li><strong>Microsoft Store</strong> — a Windows path that often opens an app window, not the python.org installer. Do not use it today.</li>
</ul>

<h2>Preparation — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Not used today:</strong> the Microsoft Store, <code>pip install paho-mqtt</code>, SQLite, Laragon, <code>php artisan</code>, Arduino IDE, or new cables. Node-RED from FS-38 may stay open, but do not change it.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen; nearby text should stay readable.</p>

<h2>Why Python on the PC, not on the ESP32</h2>
HTML
            .$why.<<<'HTML'
<p>The ESP32 already knows how to send temperature and obey commands. What is still missing on the desk is a PC program that will later receive that data. Python is the language the FULLSTACK path uses from today.</p>
<p>Do not mix: installing Python <strong>plus</strong> immediately subscribing to MQTT. One step, one proof — today the proof is the version and the script.</p>

<h2>Download the installer from python.org</h2>
HTML
            .$download.<<<'HTML'
<p><strong>Open a browser first</strong> at <a href="https://www.python.org/downloads/" target="_blank" rel="noopener noreferrer">python.org/downloads</a>. Click the large Windows button. The patch version (last number) may differ. The lock is: <strong>3.11 or newer</strong>.</p>
<p>Save the <code>.exe</code>, then double-click. If Windows shows SmartScreen, choose <em>More info</em> then <em>Run anyway</em> only for the python.org installer you just downloaded.</p>
<p><strong>macOS:</strong> open a browser to the same page, download the macOS installer, then open <strong>Terminal</strong> for <code>python3 --version</code>. <strong>Ubuntu or Debian:</strong> open <strong>Terminal</strong> first; many installs already have <code>python3</code>. If not, follow the <a href="https://docs.python.org/3/using/unix.html" target="_blank" rel="noopener noreferrer">Unix Python docs</a>. Do not use the Microsoft Store as a substitute on Windows.</p>

<h2>Tick PATH, finish the wizard</h2>
HTML
            .$path.<<<'HTML'
<p>The first wizard page shows a small box at the bottom: <strong>Add python.exe to PATH</strong>. Tick it <strong>before</strong> Install Now. Accept the defaults. Click Next until Finish.</p>
<p>After Finish, <strong>close every old PowerShell window</strong>. The new PATH is only read by a newly opened window.</p>

<h2>Open PowerShell, check python and pip</h2>
HTML
            .$version.<<<'HTML'
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. When the text appears, press Enter.</p>
<pre><code>python --version
python -m pip --version</code></pre>
<p><strong>What to look for:</strong> a <code>Python 3.12.x</code> line, or 3.11/3.13, then a <code>pip</code> line. Your exact numbers may differ.</p>
<p>If <code>python</code> opens the Microsoft Store, or PowerShell says it is not recognised: do not install from the Store. Use the Windows fallback:</p>
<pre><code>py --version
py -m pip --version</code></pre>
<p>If <code>py</code> works but <code>python</code> does not, run the python.org installer again with PATH ticked, then a new PowerShell. Reference: <a href="https://docs.python.org/3/using/windows.html" target="_blank" rel="noopener noreferrer">Using Python on Windows</a>.</p>

<h2>Create the lab folder and venv</h2>
HTML
            .$venv.<<<'HTML'
<p><strong>Open File Explorer first</strong>, go to <strong>Documents</strong>, create the folder <code>fsiot-fs39</code>.</p>
<p>In PowerShell:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
python -m venv .venv
.\.venv\Scripts\python.exe --version</code></pre>
<p><strong>What to look for:</strong> a <code>.venv</code> folder appears in File Explorer, and the last command repeats the Python version.</p>
<p>Activating the venv is optional today. If <code>.\.venv\Scripts\Activate.ps1</code> is rejected, <strong>do not change ExecutionPolicy</strong>. Keep using <code>.\.venv\Scripts\python.exe</code> to run the script. Reference: <a href="https://docs.python.org/3/tutorial/venv.html" target="_blank" rel="noopener noreferrer">venv — Python tutorial</a>.</p>

<h2>Write the script in Notepad</h2>
HTML
            .$run.<<<'HTML'
<p><strong>Open Notepad first.</strong> Start → type <strong>Notepad</strong>. Do not start with VS Code if it is not installed — the built-in text editor is enough.</p>
<p>Paste this code. Then <strong>File → Save As</strong>. Under <em>Save as type</em> choose <em>All files</em>. File name: <code>siap_stasiun.py</code>. Folder: <code>Documents\fsiot-fs39</code>. Do not let Windows add <code>.txt</code>.</p>
<pre><code class="language-python">
HTML
            .$script.<<<'HTML'
</code></pre>
<p>The <code>import sys</code> line uses a built-in library. There is no <code>pip install</code>.</p>

<h2>Run the script, then with an argument</h2>
HTML
            .$argv.<<<'HTML'
<p>Make sure PowerShell is still in the lab folder:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe siap_stasiun.py
.\.venv\Scripts\python.exe siap_stasiun.py esp32-meja-01</code></pre>
<p><strong>What to look for:</strong> the first line is always <code>Siap terima data stasiun</code>. With no argument, the name is <code>stasiun-meja-01</code>. With an argument, the name is <code>esp32-meja-01</code>.</p>
<p>If PowerShell says it cannot find the file: check File Explorer — the name must be <code>siap_stasiun.py</code>, not <code>siap_stasiun.py.txt</code>.</p>

<h2>If python is not recognised</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>A Microsoft Store window opens.</strong> Close it. That is not the python.org installer. Download again from python.org, tick PATH.</li>
<li><strong>PATH was not ticked.</strong> Run the installer again, choose Modify or uninstall then reinstall, tick <strong>Add python.exe to PATH</strong>.</li>
<li><strong>Old PowerShell.</strong> Close all windows, open a new one, repeat <code>python --version</code>.</li>
<li><strong>The bare <code>pip</code> command fails.</strong> Use <code>python -m pip --version</code> so the pip you use belongs to the same Python.</li>
</ol>

<h2 id="fsiot-python-checklist">Checklist before FS-40</h2>
<p>Tick an item only after you have actually done it. Target: <strong>10/10</strong>. Progress stays in this browser and is not sent to the server.</p>
<ul id="fsiot-python-checklist-items">
<li>The browser opens python.org, not the Microsoft Store.</li>
<li>Add python.exe to PATH is ticked before Next.</li>
<li>A new PowerShell shows <code>python --version</code> 3.11 or newer.</li>
<li><code>python -m pip --version</code> shows a pip line.</li>
<li>The folder <code>Documents\fsiot-fs39</code> exists.</li>
<li><code>python -m venv .venv</code> creates a <code>.venv</code> folder.</li>
<li>The file <code>siap_stasiun.py</code> is saved, not <code>.txt</code>.</li>
<li>The script prints <code>Siap terima data stasiun</code>.</li>
<li>The argument <code>esp32-meja-01</code> is printed as the station name.</li>
<li>I did not run <code>pip install paho-mqtt</code> today.</li>
</ul>
<p><strong>How to check readiness:</strong> tell it in your own words: download → PATH → version → venv → script. In FS-40, a script in this same folder will subscribe to MQTT.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Installing from the Microsoft Store.</strong> Close it, repeat from python.org.</li>
<li><strong>Skipping the PATH tick.</strong> <code>python</code> is not recognised.</li>
<li><strong>A <code>.py.txt</code> file.</strong> In Save As choose All files.</li>
<li><strong><code>pip install paho-mqtt</code> today.</strong> That waits for FS-40.</li>
<li><strong>Changing ExecutionPolicy because Activate.ps1 was rejected.</strong> Use <code>.venv\Scripts\python.exe</code> directly.</li>
<li><strong>Opening Arduino IDE for this lab.</strong> The ESP32 may stay unplugged.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why not MQTT straight away?</h3>
<p>If PATH is broken, an MQTT subscriber fails with a confusing message. Today we make sure Python runs.</p>
<h3>Why a venv, not just global Python?</h3>
<p>The venv keeps this lab’s libraries in one folder. FS-40 will pin versions there.</p>
<h3>Is Python 3.13 allowed?</h3>
<p>Yes, as long as it is 3.11 or newer. Not 2.7.</p>
<h3>May I use VS Code?</h3>
<p>Yes, after the script runs in Notepad + PowerShell. Text editors are taught more deeply in a later module.</p>
<h3>Does this replace Node-RED?</h3>
<p>No. Node-RED stays the visual rule brain. Python complements it as a data receiver.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://www.python.org/downloads/" target="_blank" rel="noopener noreferrer">Python Downloads</a>. Python Software Foundation. Python is a trademark of the Python Software Foundation.</li>
<li><a href="https://docs.python.org/3/using/windows.html" target="_blank" rel="noopener noreferrer">Using Python on Windows</a></li>
<li><a href="https://docs.python.org/3/tutorial/venv.html" target="_blank" rel="noopener noreferrer">venv — Creation of virtual environments</a></li>
<li><a href="https://docs.python.org/3/using/unix.html" target="_blank" rel="noopener noreferrer">Using Python on Unix</a></li>
<li><a href="https://docs.python.org/3/library/sys.html#sys.argv" target="_blank" rel="noopener noreferrer">sys.argv</a></li>
<li>Diagrams for tool order, Python on the PC, the PATH box, venv, the script, arguments, and the check schematic — Koding Indonesia (FS-39).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> Python now runs on the PC. In <strong>FS-40</strong>, a script in the same folder subscribes to MQTT and stores rows in SQLite — a complement to Node-RED, not a replacement for today’s lab.</p>
HTML;
    }
}
