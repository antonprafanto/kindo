<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class Article117Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kodingindonesia.com')->first() ?? User::first();
        $category = Category::where('slug', 'iot-smart-device')->first() ?? Category::where('slug', 'esp32-arduino')->first();
        if (! $admin || ! $category) {
            throw new \RuntimeException('User atau kategori IoT tidak ditemukan. Jalankan DatabaseSeeder dulu.');
        }

        $slug = 'fullstack-iot-telegram-alert-ambang-stasiun';
        $existing = Article::withTrashed()->where('slug', $slug)->first();
        if ($existing?->trashed()) {
            $existing->restore();
        }

        foreach (['fullstack-iot', 'iot', 'python', 'telegram', 'mqtt', 'esp32', 'sqlite'] as $tagSlug) {
            Tag::updateOrCreate(['slug' => $tagSlug], ['name' => $tagSlug]);
        }

        $article = Article::updateOrCreate(['slug' => $slug], [
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Kirim peringatan suhu ke bot Telegram dari nol',
            'title_en' => 'Send a temperature alert to a Telegram bot from scratch',
            'excerpt' => 'FS-47 / #117: BotFather, telegram_rahasia.txt, waspada_telegram.py, sendMessage saat suhu > 30. Bukan MySQL. Bukan screenshot token.',
            'excerpt_en' => 'FS-47 / #117: BotFather, telegram_rahasia.txt, waspada_telegram.py, sendMessage when temperature > 30. Not MySQL. Not a token screenshot.',
            'body' => $this->body(),
            'body_en' => $this->bodyEn(),
            'status' => 'draft',
            'is_featured' => false,
            'published_at' => null,
            'seo_title' => 'Alert Telegram saat suhu melewati ambang — FS-47',
            'seo_title_en' => 'Telegram alert when temperature crosses the limit — FS-47',
            'seo_description' => 'Lab pemula: BotFather, token di berkas rahasia, sendMessage saat suhu di atas ambang, cooldown. Bukan MySQL, bukan screenshot token, bukan AC 220V.',
            'seo_description_en' => 'A first lab: BotFather, token in a secret file, sendMessage when temperature crosses the limit, cooldown. Not MySQL, not a token screenshot, not AC mains.',
        ]);
        $article->tags()->sync(Tag::whereIn('slug', ['fullstack-iot', 'iot', 'python', 'telegram', 'mqtt', 'esp32', 'sqlite'])->pluck('id'));

        foreach (['webp', 'jpg'] as $extension) {
            $source = public_path("images/fsiot/fs47-cover-alert.{$extension}");
            if (is_file($source)) {
                $destination = "articles/covers/fs47-cover-alert.{$extension}";
                Storage::disk('public')->put($destination, file_get_contents($source));
            }
        }
        $article->update([
            'cover_image' => 'https://kodingindonesia.com/images/fsiot/fs47-cover-alert.webp',
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
        return "paho-mqtt==2.1.0";
    }

    private function rahasia(): string
    {
        return implode("\n", [
            'TOKEN=GANTI_TOKEN',
            'CHAT_ID=GANTI_CHAT_ID',
        ]);
    }

    private function waspada(): string
    {
        return implode("\n", [
            'import json',
            'import sys',
            'import time',
            'import urllib.error',
            'import urllib.request',
            'from pathlib import Path',
            '',
            'import paho.mqtt.client as mqtt',
            '',
            'BROKER = "127.0.0.1"',
            'PORT = 1883',
            'TOPIC = "kodingindonesia/fsiot/esp32-meja-01/telemetry"',
            'CLIENT_ID = "fsiot-fs47-waspada"',
            'AMBANG = 30.0',
            'COOLDOWN_DETIK = 60',
            'BERKAS = Path(__file__).resolve().parent / "telegram_rahasia.txt"',
            'API = "https://api.telegram.org/bot"',
            '',
            '',
            'def baca_rahasia():',
            '    token = ""',
            '    chat_id = ""',
            '    if not BERKAS.is_file():',
            '        return token, chat_id',
            '    for line in BERKAS.read_text(encoding="utf-8").splitlines():',
            '        baris = line.strip()',
            '        if baris.startswith("TOKEN="):',
            '            token = baris.split("=", 1)[1].strip()',
            '        elif baris.startswith("CHAT_ID="):',
            '            chat_id = baris.split("=", 1)[1].strip()',
            '    return token, chat_id',
            '',
            '',
            'def token_siap(token, chat_id):',
            '    return token and chat_id and token != "GANTI_TOKEN" and chat_id != "GANTI_CHAT_ID"',
            '',
            '',
            'def panggil(token, metode, payload=None):',
            '    url = API + token + "/" + metode',
            '    data = None',
            '    headers = {}',
            '    if payload is not None:',
            '        data = json.dumps(payload).encode("utf-8")',
            '        headers["Content-Type"] = "application/json"',
            '    cara = "POST" if payload is not None else "GET"',
            '    req = urllib.request.Request(url, data=data, headers=headers, method=cara)',
            '    with urllib.request.urlopen(req, timeout=20) as resp:',
            '        return json.loads(resp.read().decode("utf-8"))',
            '',
            '',
            'def cari_chat():',
            '    token, _chat = baca_rahasia()',
            '    if not token or token == "GANTI_TOKEN":',
            '        print("Token atau chat_id masih placeholder.")',
            '        return',
            '    try:',
            '        jawaban = panggil(token, "getUpdates")',
            '    except urllib.error.HTTPError:',
            '        print("getUpdates gagal. Cek token di berkas rahasia, jangan screenshot.")',
            '        return',
            '    except urllib.error.URLError:',
            '        print("HTTPS ke api.telegram.org gagal. Cek internet.")',
            '        return',
            '    if not jawaban.get("ok"):',
            '        print("getUpdates gagal. Cek token di berkas rahasia, jangan screenshot.")',
            '        return',
            '    chats = []',
            '    for item in jawaban.get("result") or []:',
            '        message = item.get("message") or {}',
            '        chat = message.get("chat") or {}',
            '        if "id" in chat:',
            '            chats.append(chat["id"])',
            '    if not chats:',
            '        print("Belum ada chat. Buka bot di Telegram, kirim /start, lalu ulangi --cari-chat.")',
            '        return',
            '    print("CHAT_ID=" + str(chats[-1]))',
            '    print("Salin angka itu ke telegram_rahasia.txt. Jangan screenshot token.")',
            '',
            '',
            'def kirim_alert(userdata, suhu):',
            '    now = time.time()',
            '    if now - userdata["terakhir"] < COOLDOWN_DETIK:',
            '        print("Cooldown: alert ditahan.")',
            '        return',
            '    teks = "Suhu " + str(suhu) + " C melewati ambang " + str(AMBANG) + "."',
            '    try:',
            '        jawaban = panggil(userdata["token"], "sendMessage", {"chat_id": userdata["chat_id"], "text": teks})',
            '    except urllib.error.HTTPError:',
            '        print("sendMessage gagal. Cek chat_id. Jangan screenshot token.")',
            '        return',
            '    except urllib.error.URLError:',
            '        print("HTTPS ke api.telegram.org gagal. Cek internet.")',
            '        return',
            '    if jawaban.get("ok"):',
            '        userdata["terakhir"] = now',
            '        print("Alert terkirim ke Telegram.")',
            '    else:',
            '        print("sendMessage gagal. Cek chat_id. Jangan screenshot token.")',
            '',
            '',
            'def on_connect(client, userdata, connect_flags, reason_code, properties):',
            '    if reason_code.is_failure:',
            '        print("MQTT belum tersambung:", reason_code)',
            '        return',
            '    client.subscribe(TOPIC)',
            '    print("Waspada Telegram terbuka. Menunggu telemetri.")',
            '',
            '',
            'def on_message(client, userdata, msg):',
            '    payload = msg.payload.decode("utf-8", errors="replace")',
            '    try:',
            '        data = json.loads(payload)',
            '        suhu = float(data["temperature_c"])',
            '    except (ValueError, KeyError, TypeError, json.JSONDecodeError):',
            '        print("JSON telemetri tidak terbaca.")',
            '        return',
            '    print("Diterima:", payload)',
            '    if suhu > AMBANG:',
            '        kirim_alert(userdata, suhu)',
            '',
            '',
            'def main():',
            '    if "--cari-chat" in sys.argv:',
            '        cari_chat()',
            '        return',
            '    token, chat_id = baca_rahasia()',
            '    if not token_siap(token, chat_id):',
            '        print("Token atau chat_id masih placeholder.")',
            '        return',
            '    userdata = {"token": token, "chat_id": chat_id, "terakhir": 0.0}',
            '    client = mqtt.Client(',
            '        callback_api_version=mqtt.CallbackAPIVersion.VERSION2,',
            '        client_id=CLIENT_ID,',
            '        userdata=userdata,',
            '    )',
            '    client.on_connect = on_connect',
            '    client.on_message = on_message',
            '    try:',
            '        client.connect(BROKER, PORT, keepalive=60)',
            '    except OSError:',
            '        print("Broker belum terbuka di 127.0.0.1:1883")',
            '        raise SystemExit(1)',
            '    try:',
            '        client.loop_forever()',
            '    except KeyboardInterrupt:',
            '        print("Dihentikan.")',
            '        client.disconnect()',
            '',
            '',
            'if __name__ == "__main__":',
            '    main()',
        ]);
    }

    private function body(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $rahasia = htmlspecialchars($this->rahasia(), ENT_QUOTES, 'UTF-8');
        $waspada = htmlspecialchars($this->waspada(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs47-tools-order.png', 'Urutan lima langkah: Telegram BotFather, browser, Notepad, PowerShell script, MQTTX Publish suhu', '<strong>Urutan meja kerja (lima langkah):</strong> Telegram BotFather → browser artikel ini → Notepad menulis <code>telegram_rahasia.txt</code> dan <code>waspada_telegram.py</code> → PowerShell sampai <code>Waspada Telegram terbuka. Menunggu telemetri.</code> → MQTTX Publish suhu sampai <code>Alert terkirim ke Telegram.</code> Diagram buatan Koding Indonesia (FS-47).');
        $why = $this->figure('fs47-why-alert.png', 'Tiga kotak: tombol halaman opsional, ambang suhu, chat Telegram di HP', '<strong>Tombol halaman tidak wajib. Hari ini chat di HP.</strong> Baca dari kiri ke kanan: halaman (opsional) → ambang suhu → Telegram. Diagram buatan Koding Indonesia (FS-47).');
        $flow = $this->figure('fs47-threshold-flow.png', 'Alur kiri ke kanan: MQTTX JSON suhu, broker 1883, Python waspada, sendMessage ke HP', '<strong>Gambar utama — suhu di atas ambang, chat muncul.</strong> Baca dari kiri ke kanan: MQTTX → broker → Python → <code>sendMessage</code>. Diagram buatan Koding Indonesia (FS-47).');
        $secret = $this->figure('fs47-secret-file.png', 'Tiga kotak: BotFather token, berkas telegram_rahasia.txt, script membaca tanpa mencetak token', '<strong>Token tinggal di berkas rahasia, bukan di screenshot.</strong> Baca dari kiri ke kanan: BotFather → <code>telegram_rahasia.txt</code> → script. Diagram buatan Koding Indonesia (FS-47).');
        $updates = $this->figure('fs47-getupdates.png', 'Alur kiri ke kanan: kirim slash start, getUpdates cari-chat, salin CHAT_ID ke berkas', '<strong>Cari chat_id lewat getUpdates, bukan tebak angka.</strong> Baca dari kiri ke kanan: <code>/start</code> → <code>--cari-chat</code> → <code>CHAT_ID=</code>. Diagram buatan Koding Indonesia (FS-47).');
        $cool = $this->figure('fs47-cooldown.png', 'Tiga kotak: alert pertama terkirim, 60 detik cooldown, teks Cooldown alert ditahan', '<strong>Cooldown menahan spam ke HP.</strong> Baca dari kiri ke kanan: alert pertama → 60 detik → teks <code>Cooldown: alert ditahan.</code> Diagram buatan Koding Indonesia (FS-47).');
        $botfather = $this->figure('fs47-botfather.png', 'Ilustrasi chat BotFather menampilkan token contoh yang bukan token asli', '<strong>BotFather memberi token — jangan difoto.</strong> Token contoh <code>000000:AA-contoh-bukan-asli</code> bukan token kamu. Ilustrasi buatan Koding Indonesia (FS-47), meniru chat <a href="https://core.telegram.org/bots/features#botfather" target="_blank" rel="noopener noreferrer">BotFather</a>. Tampilan resmi tidak dipakai utuh.');
        $phone = $this->figure('fs47-phone-chat.png', 'Ilustrasi chat HP menampilkan Suhu 31.2 C melewati ambang 30.0 dan teks Alert terkirim', '<strong>Chat bot sudah muncul di HP.</strong> Teks kunci di PowerShell adalah <code>Alert terkirim ke Telegram.</code> Ilustrasi buatan Koding Indonesia (FS-47), meniru jendela Telegram. Tampilan resmi tidak dipakai utuh.');
        $troubleshooting = $this->figure('fs47-troubleshooting.png', 'Empat pemeriksaan: token, slash start, MQTTX suhu, broker atau cooldown', '<strong>Skema bantu.</strong> Token siap. <code>/start</code> sudah dikirim. MQTTX Connected. Diagram buatan Koding Indonesia (FS-47).');
        $install = $this->stepsCard([
            ['title' => 'Buka Telegram', 'text' => 'Pakai aplikasi di HP atau Telegram Desktop. Siapkan akun gratis. Cari <code>@BotFather</code>. Jangan ketik perintah Python dulu.'],
            ['title' => 'Buka browser', 'text' => 'Pakai Chrome, Firefox, Edge, atau Safari. Siapkan artikel ini sebagai panduan. Jangan tempel token di bilah alamat.'],
            ['title' => 'Buka Notepad, tulis berkas', 'text' => 'Tulis <code>telegram_rahasia.txt</code> dan <code>waspada_telegram.py</code>. All files, bukan <code>.txt</code> untuk script Python.'],
            ['title' => 'Buka PowerShell, jalankan script', 'text' => 'Start → ketik PowerShell. Tidak perlu <em>Run as administrator</em>. Jalankan <code>--cari-chat</code>, salin <code>CHAT_ID=</code>, lalu jalankan tanpa <code>--cari-chat</code>. Jendela tetap terbuka.'],
            ['title' => 'Buka MQTTX', 'text' => 'Connect ke <code>127.0.0.1:1883</code>. Publish JSON suhu <code>31.2</code> ke topic telemetri. Baru setelah script menampilkan <code>Waspada Telegram terbuka. Menunggu telemetri.</code>'],
        ], '<strong>Cara menguji hari ini:</strong> bukti sukses = PowerShell menampilkan <code>Alert terkirim ke Telegram.</code> dan chat bot di HP menampilkan suhu di atas ambang. ESP32 boleh menyala, tetapi tidak wajib.');

        return <<<'HTML'
<h2>Pendahuluan — alert ke HP, bukan hanya dashboard</h2>
<p><strong>FS-47 / #117 (ini)</strong> adalah lab peringatan. Tombol ON/OFF di halaman (FS-46) <strong>tidak wajib diulang</strong>. Hari ini tugasnya lain: <strong>kirim chat ke Telegram saat suhu melewati ambang</strong>, supaya kamu tidak harus terus menatap layar.</p>
<p><strong>Intinya:</strong> buat bot lewat BotFather, simpan token di <code>telegram_rahasia.txt</code>, jalankan <code>waspada_telegram.py</code>, sampai teks <code>Alert terkirim ke Telegram.</code> muncul setelah JSON suhu di atas 30.</p>
<p><strong>Analogi:</strong> halaman suhu adalah kaca spion: berguna kalau kamu sedang duduk di depan laptop. Telegram adalah klakson di saku: bunyi meski kamu sedang di dapur. Status data basi dan perangkat offline ditunda ke FS-48.</p>
<p>Prasyarat lab: <strong>FS-42</strong> (pintu stasiun dan Mosquitto sudah pernah jalan), <strong>akun Telegram</strong> di HP (gratis), dan kesediaan membuat bot via BotFather. FS-46 tombol dashboard <strong>tidak wajib diulang</strong> hari ini. FS-41 MariaDB <strong>tidak wajib</strong>. ESP32 <strong>boleh menyala</strong>, dan <strong>boleh dicabut</strong>. Tidak ada kabel baru, tidak ada Upload, <strong>Bukan AC 220V</strong>.</p>

<h2>Hasil yang dituju</h2>
<ul>
<li>Bot baru ada di Telegram, dibuat lewat <code>@BotFather</code>.</li>
<li>Berkas <code>telegram_rahasia.txt</code> berisi <code>TOKEN=</code> dan <code>CHAT_ID=</code> — bukan placeholder.</li>
<li>PowerShell menampilkan <code>CHAT_ID=</code> setelah <code>--cari-chat</code>.</li>
<li>PowerShell menampilkan <code>Waspada Telegram terbuka. Menunggu telemetri.</code></li>
<li>MQTTX Connected ke <code>127.0.0.1:1883</code> lalu Publish JSON suhu <code>31.2</code>.</li>
<li>PowerShell menampilkan <code>Alert terkirim ke Telegram.</code></li>
<li>Chat bot di HP menampilkan suhu di atas ambang.</li>
<li>Publish kedua segera setelah itu menampilkan <code>Cooldown: alert ditahan.</code></li>
</ul>
<p><strong>Batas lab hari ini:</strong> BotFather dari nol, token sebagai rahasia, <code>getUpdates</code> untuk <code>chat_id</code>, <code>sendMessage</code> saat ambang terlewati, cooldown 60 detik. Belum jembatan server (webhook), belum email, belum MySQL, belum membuka port ke internet. Bukti cukup = teks PowerShell + chat di Telegram. Panaskan sensor fisik adalah bonus jika papan masih menyala. <code>paho-mqtt==2.1.0</code> sudah dari FS-40. Flask dari lab sebelumnya <strong>tidak wajib dibuka</strong>.</p>

<h2>Istilah yang dipakai hari ini</h2>
<ul>
<li><strong>BotFather</strong> — akun resmi Telegram untuk membuat bot. Perintah kuncinya <code>/newbot</code>.</li>
<li><strong>Token</strong> — kunci bot. Siapa pun yang punya token bisa mengirim pesan sebagai botmu. Simpan di berkas, jangan difoto.</li>
<li><strong>chat_id</strong> — nomor percakapan. Bot hanya bisa menulis setelah kamu mengirim <code>/start</code>.</li>
<li><strong>getUpdates</strong> — metode Bot API untuk membaca pesan masuk, dipakai mencari <code>chat_id</code>.</li>
<li><strong>sendMessage</strong> — metode Bot API untuk mengirim teks ke chat.</li>
<li><strong>Ambang</strong> — batas lab <code>30.0</code> derajat Celsius. Di atas itu, script mengirim alert.</li>
<li><strong>Cooldown</strong> — jeda 60 detik supaya HP tidak kebanjiran chat.</li>
<li><strong>NC/COM/NO</strong> — kaki relay. Hari ini dibiarkan kosong. <strong>Bukan AC 220V</strong>.</li>
</ul>

<h2>Persiapan — buka tool yang benar dulu</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>, baru Notepad. Jangan ketik perintah Python dulu.</p>
<p><strong>Jangan dipakai hari ini:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC 220V, <code>file://</code>, membuka port ke internet, mengubah ExecutionPolicy, pip pustaka bot tambahan, npm, ngrok, menempel token di bilah alamat browser, atau mengulang Node-RED sebagai jalur lulus. Flask dashboard dari lab sebelumnya boleh tertutup.</p>
<p><strong>Tips ponsel:</strong> jika diagram terasa kecil, gunakan fitur perbesar pada browser. Gambar tidak perlu diketuk sampai memenuhi layar agar teks di sekitarnya tetap terbaca.</p>

<h2>Kenapa Telegram, bukan hanya dashboard</h2>
HTML
            .$why.<<<'HTML'
<p>Tombol sakelar di halaman (FS-46) berguna saat laptop terbuka, tetapi tidak wajib diulang hari ini. Alert ke HP berguna saat kamu tidak sedang menatap grafik.</p>
<p>Gudang tetap SQLite <code>stasiun.db</code> jika masih ada. Jangan menunggu MariaDB. FS-41 tetap opsional. Node-RED di FS-38 boleh tetap, tetapi <strong>bukan</strong> bukti hari ini. Status data basi dan perangkat offline adalah FS-48.</p>

<h2>Buat bot di BotFather dari nol</h2>
HTML
            .$botfather.<<<'HTML'
<p><strong>Buka Telegram</strong> di HP atau Desktop. Cari <code>@BotFather</code>. Kirim <code>/start</code> jika belum, lalu <code>/newbot</code>.</p>
<ol>
<li>BotFather minta nama tampilan. Contoh: <code>Stasiun Meja</code>.</li>
<li>Lalu minta username. Username harus unik dan <strong>berakhiran <code>bot</code></strong>, misalnya <code>stasiun_meja_kamu_bot</code>.</li>
<li>Kalau username sudah dipakai, pilih yang lain. Jangan menyerah di langkah ini.</li>
<li>BotFather membalas dengan token. <strong>Salin ke Notepad</strong>, jangan difoto, jangan dikirim ke grup.</li>
</ol>
<p>Setelah token ada, buka bot barumu (tautan dari BotFather) dan kirim <code>/start</code>. Tanpa langkah ini, <code>getUpdates</code> kosong.</p>
<p>Sumber langkah: <a href="https://core.telegram.org/bots/features#botfather" target="_blank" rel="noopener noreferrer">Telegram BotFather</a> dan <a href="https://core.telegram.org/bots" target="_blank" rel="noopener noreferrer">pengantar bot</a>.</p>

<h2>Simpan token di berkas rahasia</h2>
HTML
            .$secret.<<<'HTML'
<p><strong>Buka dulu File Explorer</strong>, folder <code>Documents\fsiot-fs39</code>. <strong>Buka dulu Notepad.</strong> File → Save As, All files, nama <code>telegram_rahasia.txt</code>.</p>
<pre><code>
HTML
            .$rahasia.<<<'HTML'
</code></pre>
<p>Ganti <code>GANTI_TOKEN</code> dengan token dari BotFather. Biarkan <code>CHAT_ID=GANTI_CHAT_ID</code> dulu. Jangan unggah berkas ini, jangan kirim ke teman, jangan tempel token di bilah alamat browser lalu screenshot.</p>

<h2>Cari chat_id lewat getUpdates</h2>
HTML
            .$updates.<<<'HTML'
<p>Bot API memakai HTTPS ke <code>api.telegram.org</code>. Metode <code>getUpdates</code> mengembalikan pesan yang sudah kamu kirim ke bot, termasuk <code>chat.id</code>. Jangan mengandalkan tebakan angka. Jangan membuka URL ber-token di browser sebagai jalur lulus — token akan terlihat di bilah alamat.</p>
<p>Script lab punya opsi <code>--cari-chat</code>. Jalankan setelah <code>/start</code>. Hasil yang dicari: satu baris <code>CHAT_ID=</code> diikuti angka. Salin angka itu ke <code>telegram_rahasia.txt</code>.</p>
<p>Dokumentasi: <a href="https://core.telegram.org/bots/api#getupdates" target="_blank" rel="noopener noreferrer">getUpdates</a>. Jembatan otomatis ke server (webhook) tidak dipakai hari ini; kalau itu pernah dipasang, <code>getUpdates</code> tidak mengisi.</p>

<h2>Tulis waspada_telegram.py</h2>
<p><code>requirements.txt</code> tetap mengunci <code>paho-mqtt==2.1.0</code> seperti FS-40. Jangan pip ke Python global. Kalau paho sudah terpasang, tidak perlu pip ulang. Jangan pip pustaka bot tambahan. <code>urllib</code> sudah ada di Python standar untuk <code>sendMessage</code>.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Buka dulu File Explorer</strong>, masuk ke <code>Documents\fsiot-fs39</code>. Folder <code>.venv</code> dari lab sebelumnya harus sudah ada. Flask dari lab sebelumnya <strong>tidak wajib dibuka</strong>.</p>
<p><strong>Buka dulu Notepad.</strong> Simpan <code>waspada_telegram.py</code> dengan kode di bawah. Save As, All files, folder <code>Documents\fsiot-fs39</code>. Jangan Save sebagai <code>.txt</code>.</p>
<pre><code class="language-python">
HTML
            .$waspada.<<<'HTML'
</code></pre>
<p>Baris <code>CallbackAPIVersion.VERSION2</code> wajib di paho-mqtt 2. Client id <code>fsiot-fs47-waspada</code> jangan dipakai dua jendela sekaligus. Script tidak mencetak token.</p>
<p>Jika paho hilang dari venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Jalankan script, biarkan jendela terbuka</h2>
<p><strong>Buka dulu PowerShell:</strong> Start → ketik <strong>PowerShell</strong> → Windows PowerShell. Tidak perlu <em>Run as administrator</em>.</p>
<p><strong>Cara menempel perintah:</strong> salin baris, klik jendela PowerShell, lalu <kbd>Ctrl</kbd>+<kbd>V</kbd> atau klik kanan. Setelah teks muncul, tekan Enter.</p>
<p>Cari <code>chat_id</code> dulu:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe waspada_telegram.py --cari-chat</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>CHAT_ID=</code> diikuti angka. Salin ke <code>telegram_rahasia.txt</code>. Kalau masih placeholder, script menulis <code>Token atau chat_id masih placeholder.</code></p>
<p>Lalu jalankan pendengar:</p>
<pre><code>.\.venv\Scripts\python.exe waspada_telegram.py</code></pre>
<p><strong>Hasil yang dicari:</strong> <code>Waspada Telegram terbuka. Menunggu telemetri.</code> Jendela ini <strong>tetap terbuka</strong>. Jangan Publish dulu. Jika <code>.\.venv\Scripts\Activate.ps1</code> ditolak, <strong>jangan ubah ExecutionPolicy</strong>.</p>
<p><strong>macOS atau Linux:</strong> buka Terminal, <code>cd ~/Documents/fsiot-fs39</code>, lalu <code>.venv/bin/python waspada_telegram.py --cari-chat</code> dan tanpa <code>--cari-chat</code>.</p>

<h2>Nyalakan MQTTX, kirim suhu sampai chat muncul</h2>
HTML
            .$flow.$phone.<<<'HTML'
<p><strong>Buka MQTTX.</strong> Connect ke <code>127.0.0.1:1883</code>. Topic Publish:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<p>Isi JSON yang dikunci untuk lulus:</p>
<pre><code>{"device_id":"esp32-meja-01","temperature_c":31.2,"humidity_pct":63.1}</code></pre>
<p>Jangan Publish dulu sebelum PowerShell menampilkan <code>Waspada Telegram terbuka. Menunggu telemetri.</code> Jika Mosquitto belum jalan, nyalakan dulu seperti FS-33. Tanpa broker, script menulis <code>Broker belum terbuka di 127.0.0.1:1883</code>.</p>
<p>Setelah JSON 31.2, PowerShell menulis <code>Alert terkirim ke Telegram.</code> Chat di HP mengikuti. Jika ESP32 masih mengirim suhu kamar di bawah 30, MQTTX tetap cukup: kamu yang menaikkan angka. Panaskan sensor adalah bonus, bukan syarat lulus. <strong>Bukan AC 220V.</strong></p>

<h2>Cooldown: jangan spam</h2>
HTML
            .$cool.<<<'HTML'
<p>Telemetri ESP32 bisa datang setiap detik. Tanpa jeda, HP kebanjiran. <code>COOLDOWN_DETIK = 60</code> menahan kiriman berikut. Publish JSON 31.2 sekali lagi segera: PowerShell menulis <code>Cooldown: alert ditahan.</code> Itu pelindung, bukan kegagalan.</p>
<p>Tunggu satu menit jika kamu ingin chat kedua. Jangan menurunkan cooldown ke 0 sebagai jalur lulus.</p>

<h2>Jika chat tidak muncul</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Token masih placeholder.</strong> Ganti <code>GANTI_TOKEN</code>. Jangan screenshot isinya.</li>
<li><strong>Belum /start.</strong> Buka bot, kirim <code>/start</code>, ulangi <code>--cari-chat</code>.</li>
<li><strong>MQTTX belum Connected, atau suhu 27.</strong> Connect dulu. Angka harus di atas <code>30.0</code>.</li>
<li><strong>Broker 1883 tertutup.</strong> Script menulis <code>Broker belum terbuka di 127.0.0.1:1883</code>. Nyalakan Mosquitto.</li>
<li><strong>Masih cooldown.</strong> Tunggu 60 detik. Teks <code>Cooldown: alert ditahan.</code> berarti script hidup.</li>
</ol>

<h2 id="fsiot-telegram-checklist">Checklist sebelum FS-48</h2>
<p>Centang setelah kamu benar-benar melakukan setiap poin. Target: <strong>10/10</strong>. Progres disimpan di browser perangkatmu dan tidak dikirim ke server.</p>
<ul id="fsiot-telegram-checklist-items">
<li>Saya punya akun Telegram dan bot dari BotFather.</li>
<li>Token tersimpan di <code>telegram_rahasia.txt</code>, bukan di screenshot.</li>
<li>Saya mengirim <code>/start</code> ke bot, lalu <code>--cari-chat</code> menampilkan <code>CHAT_ID=</code>.</li>
<li>PowerShell menampilkan Waspada Telegram terbuka. Menunggu telemetri.</li>
<li>MQTTX Connected ke 127.0.0.1:1883 dan Publish JSON suhu 31.2.</li>
<li>PowerShell menampilkan <code>Alert terkirim ke Telegram.</code></li>
<li>Chat bot di HP menampilkan suhu di atas ambang.</li>
<li>Publish kedua segera menampilkan <code>Cooldown: alert ditahan.</code></li>
<li>Saya tidak mengubah ExecutionPolicy.</li>
<li>Saya tidak memakai MySQL atau AC 220V hari ini.</li>
</ul>
<p><strong>Cara memeriksa kesiapan:</strong> ceritakan dengan kata-katamu: BotFather → berkas rahasia → <code>getUpdates</code> → PowerShell tetap terbuka → MQTTX 31.2 → <code>sendMessage</code> → chat di HP. Pada FS-48, dashboard mulai jujur soal data basi. MariaDB tetap opsional.</p>

<h2>Kesalahan yang sering terjadi</h2>
<ul>
<li><strong>Screenshot token.</strong> Siapa pun yang melihat foto bisa mengirim atas nama botmu. Pakai berkas rahasia.</li>
<li><strong>Lupa /start.</strong> <code>getUpdates</code> kosong. Kirim <code>/start</code> dulu.</li>
<li><strong>Menempel token di browser.</strong> Token terlihat di bilah alamat. Pakai <code>--cari-chat</code>.</li>
<li><strong>Suhu masih 27.</strong> Ambang lab 30. Naikkan angka di MQTTX, atau panaskan sensor sebagai bonus.</li>
<li><strong>Mengulang Node-RED sebagai bukti.</strong> FS-38 boleh hidup; bukti hari ini adalah chat Telegram.</li>
<li><strong>Mengubah ExecutionPolicy.</strong> Tetap pakai <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Membuka port atau ngrok.</strong> Bot API dihubungi keluar oleh Python. HP tidak perlu menembus firewall rumahan.</li>
</ul>

<h2>Pertanyaan yang sering muncul</h2>
<h3>Kenapa jangan screenshot token?</h3>
<p>Token adalah kunci bot. Dokumentasi Telegram menyebutnya harus disimpan aman. Foto di galeri atau chat grup sama dengan membagikan kunci.</p>
<h3>ESP32 wajib menyala?</h3>
<p>Tidak. JSON di MQTTX cukup. Panaskan DHT22 adalah bonus. <strong>Bukan AC 220V.</strong></p>
<h3>Wajib MySQL?</h3>
<p>Tidak. SQLite cukup jika masih ada. FS-41 tetap opsional.</p>
<h3>Wajib buka Flask dashboard?</h3>
<p>Tidak. <code>waspada_telegram.py</code> berdiri sendiri. Tombol FS-46 boleh tertutup.</p>
<h3>Kenapa bukan Node-RED?</h3>
<p>FS-38 sudah mengajarkan ambang visual. Hari ini yang baru adalah bot Telegram dari nol plus cooldown di Python.</p>
<h3>Kalau saya tidak mau Telegram?</h3>
<p>Jembatan ke server lain (webhook) atau email bisa disebut sebagai alternatif, tetapi bukan jalur lulus. Lab ini mengunci Bot API <code>sendMessage</code>.</p>
<h3>Kenapa cooldown?</h3>
<p>Supaya satu deret telemetri tidak menjadi puluhan chat. Teks <code>Cooldown: alert ditahan.</code> adalah pelindung.</p>
<h3>Apakah data basi atau perangkat offline hari ini?</h3>
<p>Tidak. Itu FS-48.</p>
<h3>Kenapa urllib, bukan pustaka bot?</h3>
<p>Supaya tidak ada pip baru. <a href="https://core.telegram.org/bots/api#sendmessage" target="_blank" rel="noopener noreferrer">sendMessage</a> hanya butuh HTTPS POST JSON.</p>

<h2>Sumber</h2>
<ul>
<li><a href="https://core.telegram.org/bots" target="_blank" rel="noopener noreferrer">Telegram Bots: an introduction for developers</a></li>
<li><a href="https://core.telegram.org/bots/features#botfather" target="_blank" rel="noopener noreferrer">BotFather, creating and managing bots</a></li>
<li><a href="https://core.telegram.org/bots/api" target="_blank" rel="noopener noreferrer">Telegram Bot API</a></li>
<li><a href="https://core.telegram.org/bots/api#sendmessage" target="_blank" rel="noopener noreferrer">sendMessage</a></li>
<li><a href="https://core.telegram.org/bots/api#getupdates" target="_blank" rel="noopener noreferrer">getUpdates</a></li>
<li><a href="https://docs.python.org/3/library/urllib.request.html" target="_blank" rel="noopener noreferrer">urllib.request — Python docs</a></li>
<li><a href="https://eclipse.dev/paho/files/paho.mqtt.python/html/" target="_blank" rel="noopener noreferrer">Eclipse Paho MQTT Python</a> (EPL-2.0)</li>
<li><a href="https://pypi.org/project/paho-mqtt/2.1.0/" target="_blank" rel="noopener noreferrer">paho-mqtt 2.1.0 on PyPI</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li>Diagram urutan tools, dashboard versus HP, alur ambang, berkas rahasia, getUpdates, cooldown, skema periksa — Koding Indonesia (FS-47). Ilustrasi BotFather dan chat HP — Koding Indonesia (FS-47).</li>
</ul>

<h2>Selanjutnya</h2>
<p><strong>Ringkasnya:</strong> bot Telegram sudah mengirim <code>sendMessage</code> saat suhu melewati ambang. Token di berkas rahasia, cooldown menahan spam. Pada <strong>FS-48</strong>, dashboard mulai jujur soal data basi dan perangkat offline. Jangan screenshot token. MariaDB tetap opsional.</p>
HTML;
    }

    private function bodyEn(): string
    {
        $requirements = htmlspecialchars($this->requirements(), ENT_QUOTES, 'UTF-8');
        $rahasia = htmlspecialchars($this->rahasia(), ENT_QUOTES, 'UTF-8');
        $waspada = htmlspecialchars($this->waspada(), ENT_QUOTES, 'UTF-8');
        $tools = $this->figure('fs47-tools-order.png', 'Five-step order: Telegram BotFather, browser, Notepad, PowerShell script, MQTTX Publish temperature', '<strong>Desk order (five steps):</strong> Telegram BotFather → this article in a browser → Notepad writes <code>telegram_rahasia.txt</code> and <code>waspada_telegram.py</code> → PowerShell until <code>Waspada Telegram terbuka. Menunggu telemetri.</code> → MQTTX Publish a temperature until <code>Alert terkirim ke Telegram.</code> Diagram by Koding Indonesia (FS-47).');
        $why = $this->figure('fs47-why-alert.png', 'Three boxes: optional page buttons, temperature threshold, Telegram chat on a phone', '<strong>Page buttons are optional. Today a chat on the phone.</strong> Read left to right: the page (optional) → threshold → Telegram. Diagram by Koding Indonesia (FS-47).');
        $flow = $this->figure('fs47-threshold-flow.png', 'Left-to-right flow: MQTTX JSON temperature, broker 1883, Python watcher, sendMessage to the phone', '<strong>Main figure — temperature above the limit, a chat appears.</strong> Read left to right: MQTTX → broker → Python → <code>sendMessage</code>. Diagram by Koding Indonesia (FS-47).');
        $secret = $this->figure('fs47-secret-file.png', 'Three boxes: BotFather token, telegram_rahasia.txt file, script reads without printing the token', '<strong>The token stays in a secret file, not in a screenshot.</strong> Read left to right: BotFather → <code>telegram_rahasia.txt</code> → the script. Diagram by Koding Indonesia (FS-47).');
        $updates = $this->figure('fs47-getupdates.png', 'Left-to-right flow: send slash start, getUpdates find-chat, copy CHAT_ID into the file', '<strong>Find chat_id with getUpdates, do not guess the number.</strong> Read left to right: <code>/start</code> → <code>--cari-chat</code> → <code>CHAT_ID=</code>. Diagram by Koding Indonesia (FS-47).');
        $cool = $this->figure('fs47-cooldown.png', 'Three boxes: first alert sent, 60 second cooldown, Cooldown alert held text', '<strong>Cooldown holds spam off the phone.</strong> Read left to right: first alert → 60 seconds → text <code>Cooldown: alert ditahan.</code> Diagram by Koding Indonesia (FS-47).');
        $botfather = $this->figure('fs47-botfather.png', 'Illustration of a BotFather chat showing a sample token that is not a real token', '<strong>BotFather gives a token — do not photograph it.</strong> The sample token <code>000000:AA-contoh-bukan-asli</code> is not yours. Illustration by Koding Indonesia (FS-47), mimicking a <a href="https://core.telegram.org/bots/features#botfather" target="_blank" rel="noopener noreferrer">BotFather</a> chat. Official screenshots are not used as-is.');
        $phone = $this->figure('fs47-phone-chat.png', 'Illustration of a phone chat showing Suhu 31.2 C melewati ambang 30.0 and Alert terkirim text', '<strong>The bot chat is already on the phone.</strong> The locked PowerShell line is <code>Alert terkirim ke Telegram.</code> Illustration by Koding Indonesia (FS-47), mimicking a Telegram window. Official screenshots are not used as-is.');
        $troubleshooting = $this->figure('fs47-troubleshooting.png', 'Four checks: token, slash start, MQTTX temperature, broker or cooldown', '<strong>Helper diagram.</strong> Token ready. <code>/start</code> already sent. MQTTX Connected. Diagram by Koding Indonesia (FS-47).');
        $install = $this->stepsCard([
            ['title' => 'Open Telegram', 'text' => 'Use the phone app or Telegram Desktop. Have a free account ready. Search for <code>@BotFather</code>. Do not type Python commands yet.'],
            ['title' => 'Open a browser', 'text' => 'Use Chrome, Firefox, Edge, or Safari. Keep this article open as the guide. Do not paste the token into the address bar.'],
            ['title' => 'Open Notepad, write the files', 'text' => 'Write <code>telegram_rahasia.txt</code> and <code>waspada_telegram.py</code>. All files, not <code>.txt</code> for the Python script.'],
            ['title' => 'Open PowerShell, run the script', 'text' => 'Start → type PowerShell. You do not need <em>Run as administrator</em>. Run <code>--cari-chat</code>, copy <code>CHAT_ID=</code>, then run without <code>--cari-chat</code>. Keep the window open.'],
            ['title' => 'Open MQTTX', 'text' => 'Connect to <code>127.0.0.1:1883</code>. Publish JSON temperature <code>31.2</code> to the telemetry topic. Only after the script shows <code>Waspada Telegram terbuka. Menunggu telemetri.</code>'],
        ], '<strong>How to test today:</strong> success = PowerShell shows <code>Alert terkirim ke Telegram.</code> and the bot chat on the phone shows a temperature above the limit. The ESP32 may be on, but it is not required.');

        return <<<'HTML'
<h2>Introduction — an alert on the phone, not only a dashboard</h2>
<p><strong>FS-47 / #117 (this article)</strong> is the alert lab. The ON/OFF buttons on the page (FS-46) <strong>do not have to be repeated</strong>. Today the job is different: <strong>send a Telegram chat when temperature crosses the limit</strong>, so you do not have to keep staring at the screen.</p>
<p><strong>In short:</strong> create a bot through BotFather, store the token in <code>telegram_rahasia.txt</code>, run <code>waspada_telegram.py</code>, until the text <code>Alert terkirim ke Telegram.</code> appears after a temperature JSON above 30.</p>
<p><strong>Analogy:</strong> the temperature page is a rear-view mirror: useful while you sit in front of the laptop. Telegram is a horn in your pocket: it sounds even if you are in the kitchen. Stale data and offline device status wait until FS-48.</p>
<p>Lab prerequisites: <strong>FS-42</strong> (the station door and Mosquitto have run before), a <strong>Telegram account</strong> on the phone (free), and a willingness to create a bot via BotFather. The FS-46 dashboard buttons <strong>do not have to be repeated</strong> today. FS-41 MariaDB is <strong>not required</strong>. The ESP32 <strong>may stay on</strong>, and <strong>may be unplugged</strong>. No new wires, no Upload, <strong>Not AC mains</strong>.</p>

<h2>Visible result</h2>
<ul>
<li>A new bot exists in Telegram, created through <code>@BotFather</code>.</li>
<li>The file <code>telegram_rahasia.txt</code> holds <code>TOKEN=</code> and <code>CHAT_ID=</code> — not placeholders.</li>
<li>PowerShell shows <code>CHAT_ID=</code> after <code>--cari-chat</code>.</li>
<li>PowerShell shows <code>Waspada Telegram terbuka. Menunggu telemetri.</code></li>
<li>MQTTX is Connected to <code>127.0.0.1:1883</code> then Publishes JSON temperature <code>31.2</code>.</li>
<li>PowerShell shows <code>Alert terkirim ke Telegram.</code></li>
<li>The bot chat on the phone shows a temperature above the limit.</li>
<li>A second Publish right after that shows <code>Cooldown: alert ditahan.</code></li>
</ul>
<p><strong>Today’s lab boundary:</strong> BotFather from scratch, the token kept private, <code>getUpdates</code> for <code>chat_id</code>, <code>sendMessage</code> when the limit is crossed, a 60-second cooldown. No server bridge (webhook) yet, no email yet, no MySQL, no port opened to the internet. Proof is enough = PowerShell text + the Telegram chat. Warming a physical sensor is a bonus if the board is still on. <code>paho-mqtt==2.1.0</code> already came from FS-40. Flask from earlier labs <strong>does not have to stay open</strong>.</p>

<h2>Terms used today</h2>
<ul>
<li><strong>BotFather</strong> — the official Telegram account for creating bots. The locked command is <code>/newbot</code>.</li>
<li><strong>Token</strong> — the bot key. Anyone who has the token can send messages as your bot. Keep it in a file, not in a photo.</li>
<li><strong>chat_id</strong> — the conversation number. The bot can write only after you send <code>/start</code>.</li>
<li><strong>getUpdates</strong> — the Bot API method that reads incoming messages, used to find <code>chat_id</code>.</li>
<li><strong>sendMessage</strong> — the Bot API method that sends text to a chat.</li>
<li><strong>Threshold</strong> — today’s limit <code>30.0</code> degrees Celsius. Above that, the script sends an alert.</li>
<li><strong>Cooldown</strong> — a 60-second pause so the phone is not flooded with chats.</li>
<li><strong>NC/COM/NO</strong> — relay pins. Leave them empty today. <strong>Not AC mains</strong>.</li>
</ul>

<h2>Setup — open the right tools first</h2>
HTML
            .$tools.$install.<<<'HTML'
<p><strong>Open File Explorer first</strong>, go into <code>Documents\fsiot-fs39</code>, then Notepad. Do not type Python commands yet.</p>
<p><strong>Do not use today:</strong> MySQL/MariaDB, phpMyAdmin, Arduino IDE, AC mains, <code>file://</code>, opening a port to the internet, changing ExecutionPolicy, pip of an extra bot library, npm, ngrok, pasting the token into the browser address bar, or repeating Node-RED as the pass path. The Flask dashboard from earlier labs may stay closed.</p>
<p><strong>Phone tip:</strong> if a diagram feels small, use the browser zoom. You do not need to tap the image until it fills the screen, so the text around it stays readable.</p>

<h2>Why Telegram, not only the dashboard</h2>
HTML
            .$why.<<<'HTML'
<p>Switch buttons on the page (FS-46) help while the laptop is open, but they do not have to be repeated today. A phone alert helps when you are not watching the chart.</p>
<p>The warehouse stays SQLite <code>stasiun.db</code> if it is still there. Do not wait for MariaDB. FS-41 stays optional. Node-RED from FS-38 may stay open, but it is <strong>not</strong> today’s proof. Stale data and an offline device are FS-48.</p>

<h2>Create a bot in BotFather from scratch</h2>
HTML
            .$botfather.<<<'HTML'
<p><strong>Open Telegram</strong> on the phone or Desktop. Search for <code>@BotFather</code>. Send <code>/start</code> if needed, then <code>/newbot</code>.</p>
<ol>
<li>BotFather asks for a display name. Example: <code>Stasiun Meja</code>.</li>
<li>Then it asks for a username. The username must be unique and <strong>end with <code>bot</code></strong>, for example <code>stasiun_meja_kamu_bot</code>.</li>
<li>If the username is taken, pick another. Do not stop at this step.</li>
<li>BotFather replies with a token. <strong>Copy it into Notepad</strong>, do not photograph it, do not send it to a group.</li>
</ol>
<p>After the token exists, open your new bot (the link from BotFather) and send <code>/start</code>. Without this step, <code>getUpdates</code> is empty.</p>
<p>Step sources: <a href="https://core.telegram.org/bots/features#botfather" target="_blank" rel="noopener noreferrer">Telegram BotFather</a> and the <a href="https://core.telegram.org/bots" target="_blank" rel="noopener noreferrer">bot introduction</a>.</p>

<h2>Store the token in a secret file</h2>
HTML
            .$secret.<<<'HTML'
<p><strong>Open File Explorer first</strong>, folder <code>Documents\fsiot-fs39</code>. <strong>Open Notepad first.</strong> File → Save As, All files, name <code>telegram_rahasia.txt</code>.</p>
<pre><code>
HTML
            .$rahasia.<<<'HTML'
</code></pre>
<p>Replace <code>GANTI_TOKEN</code> with the token from BotFather. Leave <code>CHAT_ID=GANTI_CHAT_ID</code> for now. Do not upload this file, do not send it to a friend, and do not paste the token into the browser address bar and then take a screenshot.</p>

<h2>Find chat_id with getUpdates</h2>
HTML
            .$updates.<<<'HTML'
<p>The Bot API uses HTTPS to <code>api.telegram.org</code>. The <code>getUpdates</code> method returns messages you already sent to the bot, including <code>chat.id</code>. Do not guess the number. Do not open a token URL in the browser as the pass path — the token would sit in the address bar.</p>
<p>The lab script has a <code>--cari-chat</code> option. Run it after <code>/start</code>. The result to look for: one <code>CHAT_ID=</code> line followed by a number. Copy that number into <code>telegram_rahasia.txt</code>.</p>
<p>Docs: <a href="https://core.telegram.org/bots/api#getupdates" target="_blank" rel="noopener noreferrer">getUpdates</a>. An automatic server bridge (webhook) is not used today; if one was set earlier, <code>getUpdates</code> will not fill.</p>

<h2>Write waspada_telegram.py</h2>
<p><code>requirements.txt</code> still pins <code>paho-mqtt==2.1.0</code> as in FS-40. Do not pip into global Python. If paho is already installed, you do not need to pip again. Do not pip an extra bot library. <code>urllib</code> is already in standard Python for <code>sendMessage</code>.</p>
<pre><code>
HTML
            .$requirements.<<<'HTML'
</code></pre>
<p><strong>Open File Explorer first</strong>, go into <code>Documents\fsiot-fs39</code>. The <code>.venv</code> folder from earlier labs must already be there. Flask from earlier labs <strong>does not have to stay open</strong>.</p>
<p><strong>Open Notepad first.</strong> Save <code>waspada_telegram.py</code> with the code below. Save As, All files, lab folder <code>Documents\fsiot-fs39</code>. Do not Save it as <code>.txt</code>.</p>
<pre><code class="language-python">
HTML
            .$waspada.<<<'HTML'
</code></pre>
<p>The <code>CallbackAPIVersion.VERSION2</code> line is required on paho-mqtt 2. Do not use client id <code>fsiot-fs47-waspada</code> in two windows at once. The script does not print the token.</p>
<p>If paho is missing from the venv:</p>
<pre><code>.\.venv\Scripts\python.exe -m pip install -r requirements.txt</code></pre>

<h2>Run the script, keep the window open</h2>
<p><strong>Open PowerShell first:</strong> Start → type <strong>PowerShell</strong> → Windows PowerShell. You do not need <em>Run as administrator</em>.</p>
<p><strong>How to paste a command:</strong> copy the line, click the PowerShell window, then <kbd>Ctrl</kbd>+<kbd>V</kbd> or right-click. After the text appears, press Enter.</p>
<p>Find <code>chat_id</code> first:</p>
<pre><code>cd "$env:USERPROFILE\Documents\fsiot-fs39"
.\.venv\Scripts\python.exe waspada_telegram.py --cari-chat</code></pre>
<p><strong>Result to look for:</strong> <code>CHAT_ID=</code> followed by a number. Copy it into <code>telegram_rahasia.txt</code>. If it is still a placeholder, the script writes <code>Token atau chat_id masih placeholder.</code></p>
<p>Then run the listener:</p>
<pre><code>.\.venv\Scripts\python.exe waspada_telegram.py</code></pre>
<p><strong>Result to look for:</strong> <code>Waspada Telegram terbuka. Menunggu telemetri.</code> Keep this window <strong>open</strong>. Do not Publish yet. If <code>.\.venv\Scripts\Activate.ps1</code> is blocked, <strong>do not change ExecutionPolicy</strong>.</p>
<p><strong>macOS or Linux:</strong> open Terminal, <code>cd ~/Documents/fsiot-fs39</code>, then <code>.venv/bin/python waspada_telegram.py --cari-chat</code> and without <code>--cari-chat</code>.</p>

<h2>Start MQTTX, send a temperature until the chat appears</h2>
HTML
            .$flow.$phone.<<<'HTML'
<p><strong>Open MQTTX.</strong> Connect to <code>127.0.0.1:1883</code>. Publish topic:</p>
<pre><code>kodingindonesia/fsiot/esp32-meja-01/telemetry</code></pre>
<p>Locked JSON for the pass path:</p>
<pre><code>{"device_id":"esp32-meja-01","temperature_c":31.2,"humidity_pct":63.1}</code></pre>
<p>Do not Publish until PowerShell shows <code>Waspada Telegram terbuka. Menunggu telemetri.</code> If Mosquitto is not running, start it as in FS-33. Without a broker, the script writes <code>Broker belum terbuka di 127.0.0.1:1883</code>.</p>
<p>After the 31.2 JSON, PowerShell writes <code>Alert terkirim ke Telegram.</code> The phone chat follows. If the ESP32 is still sending a room temperature below 30, MQTTX is still enough: you raise the number. Warming the sensor is a bonus, not the pass gate. <strong>Not AC mains.</strong></p>

<h2>Cooldown: do not spam</h2>
HTML
            .$cool.<<<'HTML'
<p>ESP32 telemetry can arrive every second. Without a pause, the phone floods. <code>COOLDOWN_DETIK = 60</code> holds the next send. Publish JSON 31.2 again right away: PowerShell writes <code>Cooldown: alert ditahan.</code> That is a guard, not a failure.</p>
<p>Wait one minute if you want a second chat. Do not drop the cooldown to 0 as the pass path.</p>

<h2>If the chat does not appear</h2>
HTML
            .$troubleshooting.<<<'HTML'
<ol>
<li><strong>Token still a placeholder.</strong> Replace <code>GANTI_TOKEN</code>. Do not screenshot the contents.</li>
<li><strong>No /start yet.</strong> Open the bot, send <code>/start</code>, repeat <code>--cari-chat</code>.</li>
<li><strong>MQTTX not Connected, or temperature 27.</strong> Connect first. The number must be above <code>30.0</code>.</li>
<li><strong>Broker 1883 closed.</strong> The script writes <code>Broker belum terbuka di 127.0.0.1:1883</code>. Start Mosquitto.</li>
<li><strong>Still in cooldown.</strong> Wait 60 seconds. The text <code>Cooldown: alert ditahan.</code> means the script is alive.</li>
</ol>

<h2 id="fsiot-telegram-checklist">Checklist before FS-48</h2>
<p>Tick only after you actually did each item. Target: <strong>10/10</strong>. Progress stays in this device’s browser and is not sent to the server.</p>
<ul id="fsiot-telegram-checklist-items">
<li>I have a Telegram account and a bot from BotFather.</li>
<li>The token is stored in <code>telegram_rahasia.txt</code>, not in a screenshot.</li>
<li>I sent <code>/start</code> to the bot, then <code>--cari-chat</code> showed <code>CHAT_ID=</code>.</li>
<li>PowerShell shows Waspada Telegram terbuka. Menunggu telemetri.</li>
<li>MQTTX is Connected to 127.0.0.1:1883 and Published JSON temperature 31.2.</li>
<li>PowerShell shows <code>Alert terkirim ke Telegram.</code></li>
<li>The bot chat on the phone shows a temperature above the limit.</li>
<li>A second Publish right away shows <code>Cooldown: alert ditahan.</code></li>
<li>I did not change ExecutionPolicy.</li>
<li>I did not use MySQL or AC mains today.</li>
</ul>
<p><strong>How to check readiness:</strong> retell it in your own words: BotFather → secret file → <code>getUpdates</code> → keep PowerShell open → MQTTX 31.2 → <code>sendMessage</code> → chat on the phone. In FS-48, the dashboard starts being honest about stale data. MariaDB stays optional.</p>

<h2>Common mistakes</h2>
<ul>
<li><strong>Screenshotting the token.</strong> Anyone who sees the photo can send as your bot. Use the secret file.</li>
<li><strong>Forgetting /start.</strong> <code>getUpdates</code> is empty. Send <code>/start</code> first.</li>
<li><strong>Pasting the token into the browser.</strong> The token sits in the address bar. Use <code>--cari-chat</code>.</li>
<li><strong>Temperature still 27.</strong> The lab limit is 30. Raise the number in MQTTX, or warm the sensor as a bonus.</li>
<li><strong>Repeating Node-RED as proof.</strong> FS-38 may stay running; today’s proof is the Telegram chat.</li>
<li><strong>Changing ExecutionPolicy.</strong> Keep using <code>.venv\Scripts\python.exe</code>.</li>
<li><strong>Opening a port or ngrok.</strong> Python calls the Bot API outbound. The phone does not need to pierce the home firewall.</li>
</ul>

<h2>Frequently asked questions</h2>
<h3>Why not screenshot the token?</h3>
<p>The token is the bot key. Telegram’s docs say to store it safely. A photo in the gallery or a group chat is the same as sharing the key.</p>
<h3>Must the ESP32 stay on?</h3>
<p>No. MQTTX JSON is enough. Warming the DHT22 is a bonus. <strong>Not AC mains.</strong></p>
<h3>Is MySQL required?</h3>
<p>No. SQLite is enough if it is still there. FS-41 stays optional.</p>
<h3>Must I open the Flask dashboard?</h3>
<p>No. <code>waspada_telegram.py</code> stands alone. The FS-46 buttons may stay closed.</p>
<h3>Why not Node-RED?</h3>
<p>FS-38 already taught a visual threshold. What is new today is a Telegram bot from scratch plus cooldown in Python.</p>
<h3>What if I do not want Telegram?</h3>
<p>A server bridge (webhook) or email can be named as an alternative, but it is not the pass path. This lab locks Bot API <code>sendMessage</code>.</p>
<h3>Why cooldown?</h3>
<p>So one telemetry burst does not become dozens of chats. The text <code>Cooldown: alert ditahan.</code> is a guard.</p>
<h3>Do we cover stale data or an offline device today?</h3>
<p>No. That is FS-48.</p>
<h3>Why urllib, not a bot library?</h3>
<p>So there is no new pip. <a href="https://core.telegram.org/bots/api#sendmessage" target="_blank" rel="noopener noreferrer">sendMessage</a> only needs an HTTPS POST of JSON.</p>

<h2>Sources</h2>
<ul>
<li><a href="https://core.telegram.org/bots" target="_blank" rel="noopener noreferrer">Telegram Bots: an introduction for developers</a></li>
<li><a href="https://core.telegram.org/bots/features#botfather" target="_blank" rel="noopener noreferrer">BotFather, creating and managing bots</a></li>
<li><a href="https://core.telegram.org/bots/api" target="_blank" rel="noopener noreferrer">Telegram Bot API</a></li>
<li><a href="https://core.telegram.org/bots/api#sendmessage" target="_blank" rel="noopener noreferrer">sendMessage</a></li>
<li><a href="https://core.telegram.org/bots/api#getupdates" target="_blank" rel="noopener noreferrer">getUpdates</a></li>
<li><a href="https://docs.python.org/3/library/urllib.request.html" target="_blank" rel="noopener noreferrer">urllib.request — Python docs</a></li>
<li><a href="https://eclipse.dev/paho/files/paho.mqtt.python/html/" target="_blank" rel="noopener noreferrer">Eclipse Paho MQTT Python</a> (EPL-2.0)</li>
<li><a href="https://pypi.org/project/paho-mqtt/2.1.0/" target="_blank" rel="noopener noreferrer">paho-mqtt 2.1.0 on PyPI</a></li>
<li><a href="https://mqttx.app/" target="_blank" rel="noopener noreferrer">MQTTX by EMQ</a> (Apache License 2.0)</li>
<li>Tool-order, dashboard versus phone, threshold flow, secret file, getUpdates, cooldown, and check diagrams — Koding Indonesia (FS-47). BotFather and phone-chat illustrations — Koding Indonesia (FS-47).</li>
</ul>

<h2>Next</h2>
<p><strong>In short:</strong> the Telegram bot already sends <code>sendMessage</code> when temperature crosses the limit. The token stays in a secret file, cooldown holds spam. In <strong>FS-48</strong>, the dashboard starts being honest about stale data and an offline device. Do not screenshot the token. MariaDB stays optional.</p>
HTML;
    }
}
