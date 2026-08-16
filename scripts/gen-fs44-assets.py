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
text(draw, 215, 130, 'HTML', 28, '#166534')
text(draw, 215, 190, 'dashboard.html', 20)
text(draw, 215, 250, 'bukan file://', 18, '#14532d')
text(draw, 215, 300, 'halaman sendiri', 18, '#166534')
box(draw, (430, 70, 770, 350), '#fff8e1', '#ffffff', 4)
text(draw, 600, 130, 'Flask', 26, '#b45309')
text(draw, 600, 190, 'satu origin', 22)
text(draw, 600, 250, ':5000 /  dan', 18, '#92400e')
text(draw, 600, 300, '/telemetry', 20, '#92400e')
box(draw, (810, 70, 1160, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 985, 125, 'Suhu', 26, '#1565c0')
text(draw, 985, 195, '27.0', 42, '#1e3a8a')
text(draw, 985, 270, 'Suhu tampil.', 18, '#1e3a8a')
text(draw, 985, 315, 'angka dari JSON', 16, '#1565c0')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'JSON kemarin. Hari ini halaman yang menampilkan angkanya.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-44 · dashboard HTML dari REST Flask', 26, '#ffffff')
text(draw, 600, 570, 'http://127.0.0.1:5000  ·  fetch  ·  bukan file://', 20, '#dbeafe')
save(image, 'fs44-cover-dashboard.jpg')
image.save(OUT / 'fs44-cover-dashboard.webp', 'WEBP', quality=85)
print('fs44-cover-dashboard.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-44 — lima langkah, jangan loncat', 'Browser dulu. File Explorer. Notepad. Flask. Baru buka halaman.')
steps = [
    ('1', 'Buka browser', 'artikel ini\njangan ketik dulu', '#fff8e1', '#f9a825'),
    ('2', 'File Explorer', 'folder fsiot-fs39\nstasiun.db ada', '#e3f2fd', '#1565c0'),
    ('3', 'Notepad', 'dashboard.html\npintu_stasiun.py', '#fff7ed', '#c2410c'),
    ('4', 'PowerShell', 'jalankan Flask\njendela tetap terbuka', '#e8f5e9', '#2e7d32'),
    ('5', 'Browser', 'http://127.0.0.1\n:5000  bukan file://', '#f3e8ff', '#6d28d9'),
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
text(draw, 700, 575, 'Catatan lab: jangan MySQL, jangan file://, jangan ubah ExecutionPolicy, jangan Chart.js.', 17, '#b45309')
save(image, 'fs44-tools-order.png')

# Why a page
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'JSON tetap data. HTML adalah wajah angkanya', 'Baca kiri ke kanan: gudang SQLite → pintu JSON → halaman suhu.')
box(draw, (70, 155, 460, 520), '#e8f5e9', '#166534')
text(draw, 265, 220, 'Gudang', 28, '#166534')
text(draw, 265, 290, 'stasiun.db', 22)
text(draw, 265, 360, 'baris suhu', 20, '#14532d')
text(draw, 265, 430, 'jangan dihapus', 20, '#14532d')
box(draw, (500, 155, 900, 520), '#fff8e1', '#b45309')
text(draw, 700, 220, 'Pintu', 28, '#b45309')
text(draw, 700, 290, 'GET /telemetry', 22)
text(draw, 700, 360, 'JSON jumlah', 20, '#92400e')
text(draw, 700, 430, 'FS-42 tetap', 20, '#92400e')
box(draw, (940, 155, 1330, 520), '#e3f2fd', '#1565c0')
text(draw, 1135, 220, 'Wajah', 28, '#1565c0')
text(draw, 1135, 290, 'dashboard.html', 22)
text(draw, 1135, 360, 'angka besar', 20, '#1e3a8a')
text(draw, 1135, 430, 'Suhu tampil.', 20, '#1e3a8a')
text(draw, 700, 585, 'Catatan lab: tombol ON/OFF dan grafik ditunda. Hari ini cukup satu angka suhu.', 17, '#b45309')
save(image, 'fs44-why-page.png')

# file:// vs http
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Jangan buka HTML lewat file:// — pakai alamat http', 'Baca kiri ke kanan: file:// ditolak fetch. http://127.0.0.1:5000 boleh.')
box(draw, (70, 155, 660, 520), '#fff1f2', '#9a3412')
text(draw, 365, 220, 'file://', 28, '#9a3412')
text(draw, 365, 290, 'dobel-klik berkas', 22)
text(draw, 365, 360, 'bukan server', 20, '#7c2d12')
text(draw, 365, 430, 'fetch ditolak', 20, '#7c2d12')
text(draw, 365, 480, 'bukan lab hari ini', 18, '#9a3412')
box(draw, (740, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1035, 220, 'http://', 28, '#166534')
text(draw, 1035, 290, '127.0.0.1:5000', 22)
text(draw, 1035, 360, 'Flask menyajikan', 20, '#14532d')
text(draw, 1035, 430, 'fetch /telemetry', 20, '#14532d')
text(draw, 1035, 480, 'satu origin', 18, '#166534')
text(draw, 700, 585, 'Catatan lab: Flask hari ini adalah server halaman. Jangan python -m http.server sebagai jalur utama.', 16, '#b45309')
save(image, 'fs44-file-vs-http.png')

# CORS / same origin
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Satu origin: halaman dan JSON dari pintu yang sama', 'Kalau HTML di port 8080 dan JSON di 5000, browser menolak. Itu CORS.')
box(draw, (70, 155, 660, 520), '#fff8e1', '#b45309')
text(draw, 365, 220, 'Aman', 28, '#b45309')
text(draw, 365, 290, 'halaman :5000', 22)
text(draw, 365, 360, 'JSON :5000', 20, '#92400e')
text(draw, 365, 430, 'fetch /telemetry', 20, '#92400e')
text(draw, 365, 480, 'tanpa pustaka CORS', 18, '#b45309')
box(draw, (740, 155, 1330, 520), '#fff1f2', '#9a3412')
text(draw, 1035, 220, 'Tertolak', 28, '#9a3412')
text(draw, 1035, 290, 'HTML :8080', 22)
text(draw, 1035, 360, 'JSON :5000', 20, '#7c2d12')
text(draw, 1035, 430, 'beda origin', 20, '#7c2d12')
text(draw, 1035, 480, 'jangan dipaksa hari ini', 18, '#9a3412')
text(draw, 700, 585, 'Catatan lab: jangan pip flask-cors. Jangan izinkan internet. Lab hanya 127.0.0.1.', 17, '#b45309')
save(image, 'fs44-origin.png')

# Flask serves both — main figure
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Gambar utama — Flask menyajikan halaman dan JSON', 'Baca kiri ke kanan: browser → /  → dashboard.html; fetch → /telemetry → SQLite')
nodes = [
    (40, 'Browser', 'ketik\n:5000', '#fff8e1', '#f9a825'),
    (310, 'GET /', 'dashboard\n.html', '#e3f2fd', '#1565c0'),
    (580, 'Flask', 'pintu_\nstasiun.py', '#fff7ed', '#c2410c'),
    (850, 'GET JSON', '/telemetry\nbaris terakhir', '#f3e8ff', '#6d28d9'),
    (1120, 'Angka', 'suhu\nSuhu tampil.', '#e8f5e9', '#166534'),
]
for left, title, body, fill, color in nodes:
    box(draw, (left, 160, left + 240, 500), fill, color)
    text(draw, left + 120, 220, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 120, 330 + line_index * 48, line, 20, '#353535')
    if left < 1120:
        arrow(draw, (left + 240, 330), (left + 270, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: satu proses Flask. Jangan buka dua port. Chart.js ditunda ke FS-45.', 16, '#b45309')
save(image, 'fs44-flask-serve.png')

# fetch flow
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'fetch mengambil JSON, lalu angka ditulis ke halaman', 'Baca kiri ke kanan: script → fetch /telemetry → JSON → id suhu-angka')
flow = [
    (50, 'Script', 'di dalam\ndashboard.html', '#fff8e1', '#f9a825'),
    (390, 'fetch', '/telemetry\nsama origin', '#e3f2fd', '#1565c0'),
    (730, 'JSON', 'baris terakhir\ntemperature_c', '#fff7ed', '#c2410c'),
    (1070, 'Layar', 'angka besar\nSuhu tampil.', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: angka suhu boleh berbeda. Yang dikunci adalah teks Suhu tampil.', 17, '#b45309')
save(image, 'fs44-fetch.png')

# Browser page illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Browser sudah menampilkan angka suhu', 'Alamat: http://127.0.0.1:5000  — ini halaman HTML, bukan file://')
box(draw, (80, 145, 1320, 620), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'http://127.0.0.1:5000', 24, '#1565c0')
box(draw, (130, 270, 1260, 560), '#f5f5f0', '#1f1f1f', 2)
text(draw, 695, 330, 'Stasiun meja', 28, '#1f1f1f')
text(draw, 695, 400, 'Suhu', 20, '#475569')
text(draw, 695, 470, '27.0', 48, '#166534')
text(draw, 695, 530, 'Suhu tampil.', 22, '#166534')
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru jendela browser. Angka suhu boleh berbeda.', 16, '#b45309')
save(image, 'fs44-browser-suhu.png')

# Address bar illustration (http not file)
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Cek bilah alamat: harus http, bukan file', 'Kalau tertulis file:///C:/Users/.../dashboard.html, tutup tab itu.')
box(draw, (80, 145, 1320, 620), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 280), '#e8f5e9', '#166534', 3)
text(draw, 700, 210, 'Benar', 22, '#166534')
text(draw, 700, 250, 'http://127.0.0.1:5000', 24, '#14532d')
box(draw, (110, 310, 1290, 430), '#fff1f2', '#9a3412', 3)
text(draw, 700, 350, 'Jangan', 22, '#9a3412')
text(draw, 700, 395, 'file:///C:/Users/.../dashboard.html', 22, '#7c2d12')
box(draw, (130, 460, 1260, 560), '#0f172a', '#1f1f1f', 2)
text(draw, 695, 510, 'Flask yang menyajikan. Dobel-klik berkas tidak dipakai.', 20, '#93c5fd')
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru bilah alamat browser. Bukan screenshot jendela resmi.', 16, '#b45309')
save(image, 'fs44-address.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika angka tidak muncul', 'Perbaiki Flask, SQLite, alamat http, lalu fetch. Jangan file://.')
checks = [
    (40, '1. Flask', 'terminal belum\nPintu stasiun', '#fff8e1', '#f9a825'),
    (380, '2. SQLite', 'stasiun.db\nkosong / hilang', '#e3f2fd', '#1565c0'),
    (720, '3. Alamat', 'masih file://\nbukan :5000', '#fff7ed', '#c2410c'),
    (1060, '4. Origin', 'HTML di 8080\nJSON di 5000', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: Flask ke 127.0.0.1:5000. Jangan buka port ke internet. Jangan Chart.js.', 17, '#b45309')
save(image, 'fs44-troubleshooting.png')
