from math import atan2, cos, sin
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


OUT = Path(__file__).resolve().parents[1] / 'public' / 'images' / 'fsiot'
OUT.mkdir(parents=True, exist_ok=True)
FONT = 'C:/Windows/Fonts/arialbd.ttf'
REGULAR = 'C:/Windows/Fonts/arial.ttf'


def font(size, bold=True):
    return ImageFont.truetype(FONT if bold else REGULAR, size)


def text(draw, x, y, label, size=30, fill='#1f1f1f'):
    draw.text((x, y), label, font=font(size), fill=fill, anchor='mm')


def box(draw, area, fill, outline='#1f1f1f', width=4):
    draw.rounded_rectangle(area, radius=18, fill=fill, outline=outline, width=width)


def header(draw, width, title, subtitle):
    box(draw, (22, 18, width - 22, 120), '#ffffff')
    text(draw, width / 2, 57, title, 34)
    text(draw, width / 2, 96, subtitle, 20, '#404040')


def arrow(draw, start, end, fill, width=8, head=22):
    draw.line([start, end], fill=fill, width=width)
    ang = atan2(end[1] - start[1], end[0] - start[0])
    p2 = (end[0] - head * cos(ang - 0.4), end[1] - head * sin(ang - 0.4))
    p3 = (end[0] - head * cos(ang + 0.4), end[1] - head * sin(ang + 0.4))
    draw.polygon([end, p2, p3], fill=fill)


def save(image, name):
    image.save(OUT / name, optimize=True)
    print(name, image.size)


# Cover
image = Image.new('RGB', (1200, 675), '#1a2744')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#2f5d8c')
box(draw, (40, 70, 390, 350), '#fff8e1', '#ffffff', 4)
text(draw, 215, 130, 'Ambang', 28, '#b45309')
text(draw, 215, 190, 'suhu > 30 C', 22)
text(draw, 215, 250, 'MQTT telemetri', 18, '#92400e')
text(draw, 215, 300, '127.0.0.1:1883', 18, '#b45309')
box(draw, (430, 70, 770, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 600, 130, 'Python', 26, '#1565c0')
text(draw, 600, 190, 'waspada_telegram.py', 18)
text(draw, 600, 250, 'sendMessage', 20, '#1e3a8a')
text(draw, 600, 300, 'cooldown 60 dtk', 18, '#1565c0')
box(draw, (810, 70, 1160, 350), '#e8f5e9', '#ffffff', 4)
text(draw, 985, 125, 'Telegram', 26, '#166534')
text(draw, 985, 195, 'chat di HP', 22, '#14532d')
text(draw, 985, 270, 'Alert terkirim', 18, '#166534')
text(draw, 985, 315, 'bukan screenshot token', 16, '#14532d')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Tombol halaman opsional. Hari ini peringatan ke HP.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-47 · BotFather dari nol, token di berkas rahasia', 24, '#ffffff')
text(draw, 600, 570, 'MQTTX  ·  urllib  ·  bukan AC 220V  ·  bukan MySQL', 18, '#dbeafe')
save(image, 'fs47-cover-alert.jpg')
image.save(OUT / 'fs47-cover-alert.webp', 'WEBP', quality=85)
print('fs47-cover-alert.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-47 — lima langkah, jangan loncat', 'Telegram dulu. Browser. Notepad. PowerShell. Baru MQTTX Publish suhu.')
steps = [
    ('1', 'Telegram', 'BotFather\n/newbot + /start', '#fff8e1', '#f9a825'),
    ('2', 'Browser', 'artikel ini\njangan ketik dulu', '#e3f2fd', '#1565c0'),
    ('3', 'Notepad', 'telegram_rahasia.txt\nwaspada_telegram.py', '#fff7ed', '#c2410c'),
    ('4', 'PowerShell', '--cari-chat\nlalu waspada', '#e8f5e9', '#2e7d32'),
    ('5', 'MQTTX', 'Publish 31.2\nAlert terkirim', '#f3e8ff', '#6d28d9'),
]
for index, (number, title, body, fill, color) in enumerate(steps):
    left = 40 + index * 272
    box(draw, (left, 150, left + 248, 500), fill, color)
    text(draw, left + 124, 195, number, 32, color)
    text(draw, left + 124, 255, title, 22, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 124, 340 + line_index * 44, line, 18, '#353535')
    if index < 4:
        arrow(draw, (left + 248, 325), (left + 272, 325), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: jangan MySQL, jangan screenshot token, jangan ubah ExecutionPolicy, jangan AC 220V.', 17, '#b45309')
save(image, 'fs47-tools-order.png')

# Why alert
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Tombol halaman tidak wajib. Hari ini chat di HP', 'Baca kiri ke kanan: halaman (opsional) → ambang suhu → Telegram.')
box(draw, (70, 155, 460, 520), '#e3f2fd', '#1565c0')
text(draw, 265, 220, 'FS-46', 28, '#1565c0')
text(draw, 265, 290, 'tombol (opsional)', 22)
text(draw, 265, 360, 'Perintah terkirim.', 18, '#1e3a8a')
text(draw, 265, 430, 'harus membuka halaman', 18, '#1e3a8a')
box(draw, (500, 155, 900, 520), '#fff8e1', '#b45309')
text(draw, 700, 220, 'Ambang', 28, '#b45309')
text(draw, 700, 290, 'suhu > 30 C', 22)
text(draw, 700, 360, 'MQTT telemetri', 20, '#92400e')
text(draw, 700, 430, 'Python memutuskan', 20, '#92400e')
box(draw, (940, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1135, 220, 'FS-47', 28, '#166534')
text(draw, 1135, 290, 'chat HP', 22)
text(draw, 1135, 360, 'Alert terkirim', 20, '#14532d')
text(draw, 1135, 430, 'sendMessage', 20, '#14532d')
text(draw, 700, 585, 'Catatan lab: Node-RED FS-38 tetap boleh. Hari ini jalur lulus adalah Python + Telegram.', 16, '#b45309')
save(image, 'fs47-why-alert.png')

# Threshold flow (main figure)
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Gambar utama — suhu di atas ambang, chat muncul', 'Baca kiri ke kanan: MQTTX JSON → Python → sendMessage → HP.')
flow = [
    (50, 'MQTTX', 'Publish\ntemperature_c 31.2', '#fff8e1', '#f9a825'),
    (390, 'Broker', '127.0.0.1\n:1883', '#e3f2fd', '#1565c0'),
    (730, 'Python', 'waspada_\ntelegram.py', '#fff7ed', '#c2410c'),
    (1070, 'Telegram', 'sendMessage\nchat di HP', '#e8f5e9', '#166534'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: ESP32 boleh dicabut. Bukti wajib adalah chat di Telegram, bukan klik relay.', 16, '#b45309')
save(image, 'fs47-threshold-flow.png')

# Secret file
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Token tinggal di berkas rahasia, bukan di screenshot', 'Baca kiri ke kanan: BotFather → telegram_rahasia.txt → script membaca, tidak mencetak.')
box(draw, (70, 155, 460, 520), '#fff8e1', '#b45309')
text(draw, 265, 220, 'BotFather', 28, '#b45309')
text(draw, 265, 290, 'token bot', 22)
text(draw, 265, 360, 'salin sekali', 20, '#92400e')
text(draw, 265, 430, 'jangan foto', 20, '#92400e')
box(draw, (500, 155, 900, 520), '#e3f2fd', '#1565c0')
text(draw, 700, 220, 'Berkas', 28, '#1565c0')
text(draw, 700, 290, 'telegram_rahasia.txt', 18)
text(draw, 700, 360, 'TOKEN=...', 20, '#1e3a8a')
text(draw, 700, 430, 'CHAT_ID=...', 20, '#1e3a8a')
box(draw, (940, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1135, 220, 'Script', 28, '#166534')
text(draw, 1135, 290, 'membaca file', 22)
text(draw, 1135, 360, 'tidak print token', 18, '#14532d')
text(draw, 1135, 430, 'urllib HTTPS', 20, '#14532d')
text(draw, 700, 585, 'Catatan lab: jangan tempel token di bilah alamat browser. Jangan unggah berkas rahasia.', 16, '#b45309')
save(image, 'fs47-secret-file.png')

# getUpdates
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Cari chat_id lewat getUpdates, bukan tebak angka', 'Baca kiri ke kanan: /start ke bot → --cari-chat → salin CHAT_ID= ke berkas.')
box(draw, (70, 155, 460, 520), '#fff8e1', '#b45309')
text(draw, 265, 220, '/start', 28, '#b45309')
text(draw, 265, 290, 'buka bot baru', 22)
text(draw, 265, 360, 'kirim sekali', 20, '#92400e')
text(draw, 265, 430, 'di Telegram', 20, '#92400e')
box(draw, (500, 155, 900, 520), '#e3f2fd', '#1565c0')
text(draw, 700, 220, 'getUpdates', 28, '#1565c0')
text(draw, 700, 290, '--cari-chat', 22)
text(draw, 700, 360, 'api.telegram.org', 18, '#1e3a8a')
text(draw, 700, 430, 'bukan webhook', 20, '#1e3a8a')
box(draw, (940, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1135, 220, 'CHAT_ID=', 28, '#166534')
text(draw, 1135, 290, 'angka chat', 22)
text(draw, 1135, 360, 'ke berkas rahasia', 18, '#14532d')
text(draw, 1135, 430, 'bukan token', 20, '#14532d')
text(draw, 700, 585, 'Catatan lab: tanpa /start, getUpdates kosong. Jangan screenshot token di bilah alamat.', 16, '#b45309')
save(image, 'fs47-getupdates.png')

# Cooldown
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Cooldown menahan spam ke HP', 'Baca kiri ke kanan: alert pertama → 60 detik → alert berikutnya.')
box(draw, (70, 155, 460, 520), '#fff8e1', '#b45309')
text(draw, 265, 220, 'Suhu 31.2', 28, '#b45309')
text(draw, 265, 290, 'di atas ambang', 22)
text(draw, 265, 360, 'sendMessage', 20, '#92400e')
text(draw, 265, 430, 'Alert terkirim', 20, '#92400e')
box(draw, (500, 155, 900, 520), '#e3f2fd', '#1565c0')
text(draw, 700, 220, '60 detik', 28, '#1565c0')
text(draw, 700, 290, 'COOLDOWN', 22)
text(draw, 700, 360, 'Publish lagi', 20, '#1e3a8a')
text(draw, 700, 430, 'ditahan', 20, '#1e3a8a')
box(draw, (940, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1135, 220, 'Teks kunci', 28, '#166534')
text(draw, 1135, 290, 'Cooldown:', 22)
text(draw, 1135, 360, 'alert ditahan.', 20, '#14532d')
text(draw, 1135, 430, 'bukan error', 20, '#14532d')
text(draw, 700, 585, 'Catatan lab: ini pelindung HP, bukan kegagalan script. Tunggu 60 detik untuk alert kedua.', 16, '#b45309')
save(image, 'fs47-cooldown.png')

# BotFather illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'BotFather memberi token — jangan difoto', 'Ilustrasi. Token contoh di bawah bukan token asli.')
box(draw, (80, 145, 1320, 650), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'Telegram  ·  @BotFather  ·  /newbot', 22, '#1565c0')
box(draw, (130, 270, 1260, 610), '#f5f5f0', '#1f1f1f', 2)
text(draw, 700, 320, 'Nama tampilan: Stasiun Meja', 20, '#1e3a8a')
text(draw, 700, 375, 'Username harus diakhiri bot', 20, '#1e3a8a')
box(draw, (180, 420, 1210, 560), '#ffffff', '#90caf9', 3)
text(draw, 700, 460, '000000:AA-contoh-bukan-asli', 22, '#9a3412')
text(draw, 700, 515, 'Salin ke telegram_rahasia.txt. Jangan screenshot.', 18, '#404040')
text(draw, 700, 685, 'Catatan lab: ilustrasi meniru chat BotFather. Tangkapan layar resmi tidak dipakai utuh.', 16, '#b45309')
save(image, 'fs47-botfather.png')

# Phone chat illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Chat bot sudah muncul di HP', 'Teks kunci: Alert terkirim ke Telegram. Isi chat menyebut suhu.')
box(draw, (380, 145, 1020, 650), '#ffffff', '#166534')
box(draw, (410, 175, 990, 245), '#ecfdf5', '#86efac', 3)
text(draw, 700, 210, 'Telegram  ·  bot stasiun', 22, '#166534')
box(draw, (430, 280, 970, 430), '#e8f5e9', '#166534', 3)
text(draw, 700, 325, 'Suhu 31.2 C melewati', 22, '#14532d')
text(draw, 700, 375, 'ambang 30.0.', 22, '#14532d')
text(draw, 700, 500, 'PowerShell:', 20, '#404040')
text(draw, 700, 555, 'Alert terkirim ke Telegram.', 22, '#166534')
text(draw, 700, 685, 'Catatan lab: ilustrasi meniru chat HP. Token tidak boleh terlihat di gambar ini.', 16, '#b45309')
save(image, 'fs47-phone-chat.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika chat tidak muncul', 'Perbaiki token, /start, MQTTX, lalu cooldown. Jangan MySQL.')
checks = [
    (40, '1. Token', 'masih GANTI_TOKEN\natau ter-screenshot', '#fff8e1', '#f9a825'),
    (380, '2. /start', 'belum kirim ke bot\ngetUpdates kosong', '#e3f2fd', '#1565c0'),
    (720, '3. MQTTX', 'belum Connected\natau suhu <= 30', '#fff7ed', '#c2410c'),
    (1060, '4. Broker', '1883 tertutup\natau cooldown', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: Flask dari lab sebelumnya tidak wajib. Jangan buka port. Jangan AC 220V.', 17, '#b45309')
save(image, 'fs47-troubleshooting.png')
