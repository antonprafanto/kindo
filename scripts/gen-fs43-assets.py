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
box(draw, (40, 70, 380, 350), '#e8f5e9', '#ffffff', 4)
text(draw, 210, 125, 'meja-01', 26, '#166534')
text(draw, 210, 185, 'esp32-meja-01', 18)
text(draw, 210, 245, 'topic /telemetry', 18, '#14532d')
text(draw, 210, 300, 'nama A', 18, '#166534')
box(draw, (410, 70, 790, 350), '#fff8e1', '#ffffff', 4)
text(draw, 600, 125, 'Flask saring', 24, '#b45309')
text(draw, 600, 185, '?device_id=', 22)
text(draw, 600, 245, 'GET jumlah 5', 20, '#92400e')
text(draw, 600, 300, 'bukan campur', 18, '#92400e')
box(draw, (820, 70, 1160, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 990, 125, 'meja-02', 26, '#1565c0')
text(draw, 990, 185, 'esp32-meja-02', 18)
text(draw, 990, 245, 'topic /telemetry', 18, '#1e3a8a')
text(draw, 990, 300, 'nama B', 18, '#1565c0')
arrow(draw, (380, 210), (410, 210), '#ffd54f', 10, 22)
arrow(draw, (790, 210), (820, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Dua nama. Satu gudang SQLite. Pintu Flask yang memisahkan.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-43 · saring dua stasiun lewat device_id', 26, '#ffffff')
text(draw, 600, 570, 'MQTTX dua topic  ·  GET ?device_id=  ·  1 papan cukup', 20, '#dbeafe')
save(image, 'fs43-cover-device.jpg')
image.save(OUT / 'fs43-cover-device.webp', 'WEBP', quality=85)
print('fs43-cover-device.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-43 — lima langkah, jangan loncat', 'Browser dulu. MQTTX langganan dua topic. Python baru setelah nama jelas.')
steps = [
    ('1', 'Buka browser', 'artikel ini\njangan ketik dulu', '#fff8e1', '#f9a825'),
    ('2', 'MQTTX', 'Connect 1883\ndua topic telemetry', '#e3f2fd', '#1565c0'),
    ('3', 'Notepad', 'isi_dua_stasiun.py\npintu_stasiun.py', '#fff7ed', '#c2410c'),
    ('4', 'PowerShell', 'isi dulu, lalu\nFlask tetap terbuka', '#e8f5e9', '#2e7d32'),
    ('5', 'Browser GET', '?device_id=\nmeja-01 vs meja-02', '#f3e8ff', '#6d28d9'),
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
save(image, 'fs43-tools-order.png')

# Why device_id
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Tanpa nama, data dua stasiun tertumpuk. Dengan nama, bisa disaring.', 'Baca kiri ke kanan: tumpukan campur → dua stasiun bernama.')
box(draw, (70, 155, 660, 520), '#fff1f2', '#9a3412')
text(draw, 365, 210, 'Campur', 28, '#9a3412')
text(draw, 365, 280, 'semua baris sama', 22)
text(draw, 365, 350, 'tidak tahu milik siapa', 20, '#7c2d12')
text(draw, 365, 420, 'filter API gagal', 20, '#7c2d12')
text(draw, 365, 480, 'satu nama untuk semua', 18, '#9a3412')
box(draw, (740, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1035, 210, 'Terpisah', 28, '#166534')
text(draw, 1035, 280, 'meja-01  |  meja-02', 22)
text(draw, 1035, 350, 'GET ?device_id=', 20, '#14532d')
text(draw, 1035, 420, 'jumlah 5 untuk meja-02', 20, '#14532d')
text(draw, 1035, 480, 'nama di JSON + topic', 18, '#166534')
text(draw, 700, 585, 'Catatan lab: identitas hari ini adalah device_id, bukan Username di MQTTX.', 17, '#b45309')
save(image, 'fs43-why-id.png')

# Topic anatomy
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Baca topic kiri ke kanan — nama stasiun ada di tengah', 'kodingindonesia / fsiot / {device_id} / telemetry')
flow = [
    (40, 'rumah', 'koding\nindonesia', '#fff8e1', '#f9a825'),
    (380, 'seri', 'fsiot', '#e3f2fd', '#1565c0'),
    (720, 'nama', 'esp32-\nmeja-02', '#fff7ed', '#c2410c'),
    (1060, 'isi', 'telemetry\natau command', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 300, 500), fill, color)
    text(draw, left + 150, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
    if left < 1060:
        arrow(draw, (left + 300, 330), (left + 340, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: ganti meja-01 menjadi meja-02 di topic dan di JSON. Jangan ganti Username MQTTX.', 17, '#b45309')
save(image, 'fs43-topic.png')

# MQTTX illustration — two telemetry subscriptions
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'MQTTX sudah berlangganan dua topic telemetry', 'Connect dulu ke 127.0.0.1:1883. Subscribe dua kali. Jangan Publish dulu.')
box(draw, (80, 145, 1320, 620), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'MQTTX  ·  127.0.0.1:1883  ·  Connected', 24, '#1565c0')
box(draw, (130, 270, 1260, 350), '#e8f5e9', '#166534', 2)
text(draw, 695, 310, 'Subscribe  kodingindonesia/fsiot/esp32-meja-01/telemetry', 20, '#166534')
box(draw, (130, 370, 1260, 450), '#e3f2fd', '#1565c0', 2)
text(draw, 695, 410, 'Subscribe  kodingindonesia/fsiot/esp32-meja-02/telemetry', 20, '#1565c0')
box(draw, (130, 470, 1260, 560), '#0f172a', '#1f1f1f', 2)
text(draw, 695, 515, 'Dua langganan. Publish belum. Nama belum boleh tertukar.', 20, '#93c5fd')
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru MQTTX oleh EMQ (Apache License 2.0). Bukan screenshot jendela resmi.', 16, '#b45309')
save(image, 'fs43-mqttx.png')

# One board, two names — KI diagram (no GPIO photo: pin labels confuse this lesson)
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Satu papan cukup — yang diganti adalah namanya', 'Baca kiri ke kanan: papan yang sama, lalu dua nama di JSON dan topic.')
box(draw, (500, 170, 900, 500), '#eceff1', '#455a64')
text(draw, 700, 230, '1 papan ESP32', 26, '#37474f')
text(draw, 700, 290, 'boleh menyala', 20, '#455a64')
text(draw, 700, 340, 'boleh dicabut', 20, '#455a64')
text(draw, 700, 400, 'bukan 2 papan baru', 20, '#455a64')
text(draw, 700, 455, 'bukan urutan kaki', 18, '#b45309')
box(draw, (70, 200, 430, 470), '#e8f5e9', '#166534')
text(draw, 250, 270, 'nama A', 26, '#166534')
text(draw, 250, 340, 'esp32-meja-01', 22)
text(draw, 250, 410, 'topic meja-01', 20, '#14532d')
box(draw, (970, 200, 1330, 470), '#e3f2fd', '#1565c0')
text(draw, 1150, 270, 'nama B', 26, '#1565c0')
text(draw, 1150, 340, 'esp32-meja-02', 22)
text(draw, 1150, 410, 'topic meja-02', 20, '#1e3a8a')
arrow(draw, (430, 335), (500, 335), '#1f1f1f', 6, 14)
arrow(draw, (900, 335), (970, 335), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: foto papan Wikimedia tidak dipakai di sini karena label GPIO mudah disalin salah.', 16, '#b45309')
save(image, 'fs43-two-names.png')

# Filter — main figure
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Gambar utama — GET ?device_id= memisahkan dua stasiun', 'Baca kiri ke kanan: browser → Flask → SQLite → JSON meja-02 jumlah 5')
nodes = [
    (40, 'Browser', 'GET\n?device_id=', '#fff8e1', '#f9a825'),
    (310, 'Flask', 'pintu_\nstasiun.py', '#e3f2fd', '#1565c0'),
    (580, 'SQLite', 'stasiun.db\ndua nama', '#fff7ed', '#c2410c'),
    (850, 'Saring', 'hanya\nmeja-02', '#f3e8ff', '#6d28d9'),
    (1120, 'JSON', 'jumlah 5\nmeja-02', '#e8f5e9', '#166534'),
]
for left, title, body, fill, color in nodes:
    box(draw, (left, 160, left + 240, 500), fill, color)
    text(draw, left + 120, 220, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 120, 330 + line_index * 48, line, 20, '#353535')
    if left < 1120:
        arrow(draw, (left + 240, 330), (left + 270, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: GET tanpa ?device_id= masih boleh. Yang dikunci hari ini adalah saringan meja-02.', 16, '#b45309')
save(image, 'fs43-filter.png')

# Browser JSON illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Browser sudah menampilkan JSON meja-02 jumlah 5', 'Alamat: http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02')
box(draw, (80, 145, 1320, 620), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'http://127.0.0.1:5000/telemetry?device_id=esp32-meja-02', 22, '#1565c0')
box(draw, (130, 270, 1260, 560), '#0f172a', '#1f1f1f', 2)
lines = [
    ('{', '#e2e8f0'),
    ('  "jumlah": 5,', '#86efac'),
    ('  "device_id": "esp32-meja-02",', '#fde68a'),
    ('  "baris": [', '#93c5fd'),
    ('    {"device_id": "esp32-meja-02", "temperature_c": 24.0},', '#cbd5e1'),
    ('    ...', '#94a3b8'),
    ('  ]', '#93c5fd'),
    ('}', '#e2e8f0'),
]
for index, (line, color) in enumerate(lines):
    draw.text((160, 292 + index * 30), line, font=font(20, bold=False), fill=color)
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru jendela browser. Angka suhu boleh berbeda. Yang dikunci adalah jumlah 5.', 16, '#b45309')
save(image, 'fs43-browser-json.png')

# Command topic matches device_id
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'POST perintah memakai device_id yang sama di topic', 'Baca kiri ke kanan: uji_perintah.py → Flask → topic meja-02/command → MQTTX')
flow = [
    (50, 'Script', 'device_id\nmeja-02', '#fff8e1', '#f9a825'),
    (390, 'Flask', 'POST\n/command', '#e3f2fd', '#1565c0'),
    (730, 'Topic', '.../meja-02\n/command', '#fff7ed', '#c2410c'),
    (1070, 'MQTTX', 'pesan di\nnama yang sama', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: perintah meja-02 tidak boleh jatuh ke topic meja-01. ESP32 boleh dicabut; MQTTX cukup.', 16, '#b45309')
save(image, 'fs43-command-topic.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika dua nama masih campur', 'Perbaiki Flask, SQLite, dua langganan MQTTX, lalu cek Username vs device_id.')
checks = [
    (40, '1. Flask', 'terminal belum\nPintu stasiun', '#fff8e1', '#f9a825'),
    (380, '2. SQLite', 'isi_dua belum\n5 baris meja-02', '#e3f2fd', '#1565c0'),
    (720, '3. MQTTX', 'baru satu topic\nbelum dua nama', '#fff7ed', '#c2410c'),
    (1060, '4. Nama', 'Username MQTTX\ndisangka device_id', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: jangan mengubah allow_anonymous Mosquitto hari ini. Jangan file://.', 17, '#b45309')
save(image, 'fs43-troubleshooting.png')
