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
text(draw, 215, 130, 'Tombol', 28, '#b45309')
text(draw, 215, 190, 'ON / OFF', 22)
text(draw, 215, 250, 'dashboard.html', 18, '#92400e')
text(draw, 215, 300, 'satu origin', 18, '#b45309')
box(draw, (430, 70, 770, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 600, 130, 'POST', 26, '#1565c0')
text(draw, 600, 190, '/command', 22)
text(draw, 600, 250, 'relay on/off', 20, '#1e3a8a')
text(draw, 600, 300, 'Mosquitto 1883', 18, '#1565c0')
box(draw, (810, 70, 1160, 350), '#e8f5e9', '#ffffff', 4)
text(draw, 985, 125, 'Status', 26, '#166534')
text(draw, 985, 195, 'Sakelar: ON', 22, '#14532d')
text(draw, 985, 270, 'Perintah terkirim.', 18, '#166534')
text(draw, 985, 315, 'GET /status', 16, '#14532d')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Kemarin garis tren. Hari ini tombol sakelar di halaman.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-46 · POST /command dari browser, bukan uji_perintah.py', 24, '#ffffff')
text(draw, 600, 570, 'http://127.0.0.1:5000  ·  MQTTX  ·  bukan AC 220V', 18, '#dbeafe')
save(image, 'fs46-cover-control.jpg')
image.save(OUT / 'fs46-cover-control.webp', 'WEBP', quality=85)
print('fs46-cover-control.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-46 — lima langkah, jangan loncat', 'Browser dulu. MQTTX. Notepad. Flask. Baru klik ON di halaman.')
steps = [
    ('1', 'Buka browser', 'artikel ini\njangan ketik dulu', '#fff8e1', '#f9a825'),
    ('2', 'MQTTX', 'Connect 1883\nSubscribe command', '#e3f2fd', '#1565c0'),
    ('3', 'Notepad', 'dashboard.html\npintu_stasiun.py', '#fff7ed', '#c2410c'),
    ('4', 'PowerShell', 'Flask tetap\nterbuka', '#e8f5e9', '#2e7d32'),
    ('5', 'Browser', 'klik ON\nPerintah terkirim.', '#f3e8ff', '#6d28d9'),
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
text(draw, 700, 575, 'Catatan lab: jangan MySQL, jangan file://, jangan ubah ExecutionPolicy, jangan AC 220V.', 17, '#b45309')
save(image, 'fs46-tools-order.png')

# Why buttons
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Kemarin garis tren. Hari ini tombol sakelar', 'Baca kiri ke kanan: grafik FS-45 → POST /command → status Sakelar.')
box(draw, (70, 155, 460, 520), '#e3f2fd', '#1565c0')
text(draw, 265, 220, 'FS-45', 28, '#1565c0')
text(draw, 265, 290, 'garis tren', 22)
text(draw, 265, 360, 'Grafik tampil.', 20, '#1e3a8a')
text(draw, 265, 430, 'melihat saja', 20, '#1e3a8a')
box(draw, (500, 155, 900, 520), '#fff8e1', '#b45309')
text(draw, 700, 220, 'Tombol', 28, '#b45309')
text(draw, 700, 290, 'ON / OFF', 22)
text(draw, 700, 360, 'fetch POST', 20, '#92400e')
text(draw, 700, 430, '/command', 20, '#92400e')
box(draw, (940, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1135, 220, 'Status', 28, '#166534')
text(draw, 1135, 290, 'Sakelar: ON', 22)
text(draw, 1135, 360, 'Perintah terkirim.', 18, '#14532d')
text(draw, 1135, 430, 'MQTTX ikut', 20, '#14532d')
text(draw, 700, 585, 'Catatan lab: grafik kemarin boleh tetap. Jangan menunggu Telegram. Itu FS-47.', 17, '#b45309')
save(image, 'fs46-why-buttons.png')

# POST flow (main figure)
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Gambar utama — klik tombol, Flask meneruskan perintah', 'Baca kiri ke kanan: ON → POST /command → Mosquitto → MQTTX.')
flow = [
    (50, 'Tombol', 'ON atau OFF\ndashboard.html', '#fff8e1', '#f9a825'),
    (390, 'POST', '/command\nJSON relay', '#e3f2fd', '#1565c0'),
    (730, 'Broker', '127.0.0.1\n:1883', '#fff7ed', '#c2410c'),
    (1070, 'MQTTX', 'relay on\ntopic command', '#e8f5e9', '#166534'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: ESP32 boleh dicabut. Bukti wajib adalah teks halaman plus MQTTX, bukan klik relay.', 16, '#b45309')
save(image, 'fs46-post-flow.png')

# Double submit
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Jaga tombol: satu klik, satu kirim', 'Baca kiri ke kanan: klik → kunci tombol → tunggu jawaban → buka lagi.')
box(draw, (70, 155, 460, 520), '#fff8e1', '#b45309')
text(draw, 265, 220, 'Klik', 28, '#b45309')
text(draw, 265, 290, 'ON sekali', 22)
text(draw, 265, 360, 'sedang = true', 20, '#92400e')
text(draw, 265, 430, 'disabled', 20, '#92400e')
box(draw, (500, 155, 900, 520), '#e3f2fd', '#1565c0')
text(draw, 700, 220, 'Kirim', 28, '#1565c0')
text(draw, 700, 290, 'fetch POST', 22)
text(draw, 700, 360, 'Mengirim…', 20, '#1e3a8a')
text(draw, 700, 430, 'tunggu JSON', 20, '#1e3a8a')
box(draw, (940, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1135, 220, 'Selesai', 28, '#166534')
text(draw, 1135, 290, 'buka tombol', 22)
text(draw, 1135, 360, 'Perintah terkirim.', 18, '#14532d')
text(draw, 1135, 430, 'atau gagal', 20, '#14532d')
text(draw, 700, 585, 'Catatan lab: dobel klik tanpa kunci mengirim dua perintah. Lab mengunci tombol selama fetch.', 16, '#b45309')
save(image, 'fs46-double-submit.png')

# Status sync
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Baca status supaya UI tidak ketinggalan', 'Baca kiri ke kanan: buka halaman → GET /status → tulis Sakelar: ON.')
flow = [
    (50, 'Buka', 'http://127.0.0.1\n:5000', '#fff8e1', '#f9a825'),
    (390, 'GET', '/status\nSQLite commands', '#e3f2fd', '#1565c0'),
    (730, 'JSON', 'relay on\natau kosong', '#fff7ed', '#c2410c'),
    (1070, 'Tulis', 'Sakelar: ON\natau belum', '#e8f5e9', '#166534'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: refresh halaman tidak boleh mengosongkan sakelar jika perintah terakhir sudah tersimpan.', 16, '#b45309')
save(image, 'fs46-status.png')

# Flask serve
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Flask menyajikan halaman, status, dan perintah', 'Baca kiri ke kanan: GET / → GET /status → POST /command → MQTT.')
nodes = [
    (40, 'GET /', 'dashboard\n.html', '#fff8e1', '#f9a825'),
    (310, 'GET', '/status\nSQLite', '#e3f2fd', '#1565c0'),
    (580, 'POST', '/command\nJSON', '#fff7ed', '#c2410c'),
    (850, 'MQTT', 'topic\ncommand', '#f3e8ff', '#6d28d9'),
    (1120, 'UI', 'Perintah\nterkirim.', '#e8f5e9', '#166534'),
]
for left, title, body, fill, color in nodes:
    box(draw, (left, 160, left + 240, 500), fill, color)
    text(draw, left + 120, 220, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 120, 330 + line_index * 48, line, 20, '#353535')
    if left < 1120:
        arrow(draw, (left + 240, 330), (left + 270, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: satu proses Flask. Jangan file://. Jangan dua port. Jangan flask-cors.', 16, '#b45309')
save(image, 'fs46-flask-serve.png')

# Browser panel illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Browser sudah menampilkan tombol sakelar', 'Alamat: http://127.0.0.1:5000  — status Perintah terkirim.')
box(draw, (80, 145, 1320, 650), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'http://127.0.0.1:5000', 24, '#1565c0')
box(draw, (130, 270, 1260, 610), '#f5f5f0', '#1f1f1f', 2)
text(draw, 280, 315, 'Stasiun meja', 24, '#1f1f1f')
text(draw, 280, 365, 'Sakelar: ON', 28, '#166534')
box(draw, (170, 410, 430, 500), '#e8f5e9', '#166534', 3)
text(draw, 300, 455, 'ON', 28, '#166534')
box(draw, (470, 410, 730, 500), '#fff1f2', '#9a3412', 3)
text(draw, 600, 455, 'OFF', 28, '#9a3412')
text(draw, 700, 555, 'Perintah terkirim.', 24, '#166534')
text(draw, 700, 685, 'Catatan lab: ilustrasi meniru jendela browser. Grafik kemarin boleh tetap di halaman yang sama.', 16, '#b45309')
save(image, 'fs46-browser-panel.png')

# MQTTX illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'MQTTX sudah menampilkan perintah relay', 'Connected 127.0.0.1:1883. Topic command. JSON relay on.')
box(draw, (80, 145, 1320, 650), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'mqttx  ·  127.0.0.1:1883  ·  Connected', 22, '#1565c0')
box(draw, (130, 270, 1260, 610), '#f5f5f0', '#1f1f1f', 2)
text(draw, 700, 320, 'kodingindonesia/fsiot/esp32-meja-01/command', 20, '#1e3a8a')
box(draw, (180, 360, 1210, 560), '#ffffff', '#90caf9', 3)
text(draw, 700, 410, '{"device_id":"esp32-meja-01","relay":"on"}', 22, '#166534')
text(draw, 700, 480, 'Subscribe dulu. Jangan tekan Publish.', 18, '#404040')
text(draw, 700, 685, 'Catatan lab: ilustrasi meniru MQTTX. Tangkapan layar resmi tidak dipakai utuh.', 16, '#b45309')
save(image, 'fs46-mqttx.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika tombol tidak merespons', 'Perbaiki MQTTX, Flask, alamat http, lalu dobel klik. Jangan MySQL.')
checks = [
    (40, '1. MQTTX', 'belum Connected\nbelum Subscribe', '#fff8e1', '#f9a825'),
    (380, '2. Flask', 'terminal belum\nPintu stasiun', '#e3f2fd', '#1565c0'),
    (720, '3. Alamat', 'masih file://\nbukan :5000', '#fff7ed', '#c2410c'),
    (1060, '4. Broker', '1883 tertutup\natau dobel klik', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: Flask ke 127.0.0.1:5000. Jangan buka port ke internet. Jangan AC 220V.', 17, '#b45309')
save(image, 'fs46-troubleshooting.png')
