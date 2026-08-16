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
box(draw, (40, 70, 390, 350), '#e8f5e9', '#ffffff', 4)
text(draw, 215, 125, 'SQLite', 28, '#166534')
text(draw, 215, 180, 'stasiun.db', 22)
text(draw, 215, 230, 'GET /telemetry', 20, '#475569')
text(draw, 215, 280, 'jumlah 10', 18, '#475569')
box(draw, (430, 70, 770, 350), '#fff8e1', '#ffffff', 4)
text(draw, 600, 125, 'Flask', 26, '#b45309')
text(draw, 600, 185, 'pintu_stasiun.py', 20)
text(draw, 600, 235, '127.0.0.1:5000', 20, '#92400e')
text(draw, 600, 285, 'flask==3.1.3', 18, '#92400e')
box(draw, (810, 70, 1160, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 985, 125, 'MQTT', 26, '#1565c0')
text(draw, 985, 185, 'POST /command', 22, '#1e3a8a')
text(draw, 985, 235, 'topic command', 18, '#1e3a8a')
text(draw, 985, 285, 'relay on', 18, '#1e3a8a')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'SQLite tetap gudang. Flask hanya pintu. Belum dashboard HTML.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-42 · REST Flask baca SQLite', 26, '#ffffff')
text(draw, 600, 570, 'Browser GET  ·  POST perintah  ·  MQTTX', 20, '#dbeafe')
save(image, 'fs42-cover-flask.jpg')
image.save(OUT / 'fs42-cover-flask.webp', 'WEBP', quality=85)
print('fs42-cover-flask.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-42 — lima langkah, jangan loncat', 'Browser dulu. MQTTX langganan command. Flask baru setelah pip.')
steps = [
    ('1', 'Buka browser', 'artikel ini\njangan pip dulu', '#fff8e1', '#f9a825'),
    ('2', 'MQTTX', 'Connect 1883\nSubscribe command', '#e3f2fd', '#1565c0'),
    ('3', 'Notepad', 'requirements\npintu_stasiun.py', '#fff7ed', '#c2410c'),
    ('4', 'PowerShell', 'pip lalu jalankan\nFlask', '#e8f5e9', '#2e7d32'),
    ('5', 'Browser GET', '5000/telemetry\nlalu uji_perintah.py', '#f3e8ff', '#6d28d9'),
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
text(draw, 700, 575, 'Catatan lab: jangan MySQL, jangan file://, jangan ubah ExecutionPolicy, jangan dashboard HTML.', 17, '#b45309')
save(image, 'fs42-tools-order.png')

# Why API
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'SQLite tetap gudang. Flask hanya pintu HTTP', 'GET membaca berkas. POST meneruskan perintah ke Mosquitto.')
box(draw, (70, 155, 660, 520), '#e8f5e9', '#166534')
text(draw, 365, 210, 'Gudang', 28, '#166534')
text(draw, 365, 280, 'stasiun.db', 22)
text(draw, 365, 350, 'satu berkas, 10 baris', 20, '#14532d')
text(draw, 365, 420, 'jangan dihapus', 20, '#14532d')
text(draw, 365, 480, 'bukan MariaDB hari ini', 18, '#166534')
box(draw, (740, 155, 1330, 520), '#fff8e1', '#b45309')
text(draw, 1035, 210, 'Pintu', 28, '#b45309')
text(draw, 1035, 280, 'Flask :5000', 22)
text(draw, 1035, 350, 'GET /telemetry', 20, '#92400e')
text(draw, 1035, 420, 'POST /command', 20, '#92400e')
text(draw, 1035, 480, 'bukan halaman cantik', 18, '#b45309')
text(draw, 700, 585, 'Catatan lab: dashboard HTML ditunda ke FS-44. Hari ini bukti = JSON di browser.', 17, '#b45309')
save(image, 'fs42-why-api.png')

# Download / official docs then pip
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Pustaka Flask dari PyPI, dokumentasi resmi dibaca dulu', 'Baca kiri ke kanan: browser → flask.palletsprojects.com → pip di venv → port 5000')
flow = [
    (50, 'Browser', 'artikel ini\njangan ketik pip', '#fff8e1', '#f9a825'),
    (390, 'Docs', 'flask.pallets\nprojects.com', '#e3f2fd', '#1565c0'),
    (730, 'pip', 'flask==3.1.3\ndi venv FS-39', '#fff7ed', '#c2410c'),
    (1070, 'Selesai', 'Pintu terbuka\n127.0.0.1:5000', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: Flask tidak punya pemasang Windows. Yang dipasang adalah baris pip terkunci.', 17, '#b45309')
save(image, 'fs42-download.png')

# MQTTX illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'MQTTX sudah berlangganan topic command', 'Connect dulu ke 127.0.0.1:1883. Subscribe. Jangan Publish dulu.')
box(draw, (80, 145, 1320, 620), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'MQTTX  ·  127.0.0.1:1883  ·  Connected', 24, '#1565c0')
box(draw, (130, 270, 1260, 360), '#e8f5e9', '#166534', 2)
text(draw, 695, 315, 'Subscribe  kodingindonesia/fsiot/esp32-meja-01/command', 20, '#166534')
box(draw, (130, 390, 1260, 560), '#0f172a', '#1f1f1f', 2)
text(draw, 695, 445, 'Menunggu pesan command...', 22, '#93c5fd')
text(draw, 695, 505, 'Publish belum. Flask yang akan mengirim.', 18, '#cbd5e1')
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru MQTTX oleh EMQ (Apache License 2.0). Bukan screenshot jendela resmi.', 16, '#b45309')
save(image, 'fs42-mqttx.png')

# pip + venv
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'venv yang sama, tambah satu baris Flask terkunci', 'Baca kiri ke kanan: folder lab → venv FS-39 → pip -r → flask 3.1.3')
flow = [
    (50, 'Folder', 'Documents\\\nfsiot-fs39', '#fff8e1', '#f9a825'),
    (390, 'venv', '.venv dari\nFS-39', '#e3f2fd', '#1565c0'),
    (730, 'pip', 'python -m pip\ninstall -r', '#fff7ed', '#c2410c'),
    (1070, 'Terkunci', 'flask\n==3.1.3', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: pakai .venv\\Scripts\\python.exe. Jika Activate.ps1 ditolak, jangan ubah ExecutionPolicy.', 17, '#b45309')
save(image, 'fs42-pip-venv.png')

# Routes — main figure
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Gambar utama — dua pintu, satu berkas Flask', 'Baca dari kiri ke kanan: browser GET → SQLite; PowerShell POST → Mosquitto')
nodes = [
    (40, 'GET', '/telemetry\nJSON 10 baris', '#e8f5e9', '#166534'),
    (310, 'Flask', 'pintu_\nstasiun.py', '#fff8e1', '#f9a825'),
    (580, 'SQLite', 'stasiun.db\ntetap ada', '#fff7ed', '#c2410c'),
    (850, 'POST', '/command\nrelay on', '#f3e8ff', '#6d28d9'),
    (1120, 'MQTTX', 'topic\ncommand', '#e3f2fd', '#1565c0'),
]
for left, title, body, fill, color in nodes:
    box(draw, (left, 160, left + 240, 500), fill, color)
    text(draw, left + 120, 220, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 120, 330 + line_index * 48, line, 20, '#353535')
    if left < 1120:
        arrow(draw, (left + 240, 330), (left + 270, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: Flask tidak menulis SQLite hari ini. Ia hanya membaca, lalu meneruskan perintah.', 17, '#b45309')
save(image, 'fs42-routes.png')

# Browser JSON illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Browser sudah menampilkan JSON jumlah 10', 'Alamat: http://127.0.0.1:5000/telemetry  — ini JSON, bukan halaman dashboard.')
box(draw, (80, 145, 1320, 620), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'http://127.0.0.1:5000/telemetry', 24, '#1565c0')
box(draw, (130, 270, 1260, 560), '#0f172a', '#1f1f1f', 2)
lines = [
    ('{', '#e2e8f0'),
    ('  "jumlah": 10,', '#86efac'),
    ('  "baris": [', '#93c5fd'),
    ('    {"id": 1, "device_id": "esp32-meja-01", "temperature_c": 27.0},', '#fde68a'),
    ('    ...', '#94a3b8'),
    ('  ]', '#93c5fd'),
    ('}', '#e2e8f0'),
]
for index, (line, color) in enumerate(lines):
    draw.text((170, 300 + index * 34), line, font=font(22, bold=False), fill=color)
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru jendela browser. Angka suhu boleh berbeda. Yang dikunci adalah jumlah 10.', 16, '#b45309')
save(image, 'fs42-browser-json.png')

# POST flow
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'POST perintah, Mosquitto meneruskan, MQTTX menampilkan', 'Baca kiri ke kanan: uji_perintah.py → Flask → 1883 → topic command')
flow = [
    (50, 'Script', 'uji_\nperintah.py', '#fff8e1', '#f9a825'),
    (390, 'Flask', 'POST\n/command', '#e3f2fd', '#1565c0'),
    (730, 'Broker', 'Mosquitto\n127.0.0.1:1883', '#fff7ed', '#c2410c'),
    (1070, 'Bukti', 'MQTTX\nrelay on', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: ESP32 boleh menyala atau dicabut. MQTTX adalah bukti wajib. Relay klik hanya jika firmware perintah masih jalan.', 16, '#b45309')
save(image, 'fs42-post-mqtt.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika JSON tidak muncul', 'Perbaiki Flask, SQLite, dan Mosquitto dulu. Jangan pip ke Python global.')
checks = [
    (40, '1. Flask', 'terminal belum\nPintu stasiun', '#fff8e1', '#f9a825'),
    (380, '2. SQLite', 'stasiun.db\nkosong / hilang', '#e3f2fd', '#1565c0'),
    (720, '3. Broker', 'Mosquitto\nbelum 1883', '#fff7ed', '#c2410c'),
    (1060, '4. Port', '5000 dipakai\nFlask lain', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: Flask ke 127.0.0.1:5000. Jangan buka port ke internet. Jangan file://.', 17, '#b45309')
save(image, 'fs42-troubleshooting.png')
