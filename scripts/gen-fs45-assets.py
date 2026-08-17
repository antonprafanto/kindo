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
text(draw, 215, 130, 'SQLite', 28, '#166534')
text(draw, 215, 190, 'stasiun.db', 20)
text(draw, 215, 250, '12 titik', 18, '#14532d')
text(draw, 215, 300, 'rentang 1 jam', 18, '#166534')
box(draw, (430, 70, 770, 350), '#fff8e1', '#ffffff', 4)
text(draw, 600, 130, 'GET', 26, '#b45309')
text(draw, 600, 190, '/history', 22)
text(draw, 600, 250, '?hours=1', 20, '#92400e')
text(draw, 600, 300, 'maks 60 titik', 18, '#92400e')
box(draw, (810, 70, 1160, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 985, 125, 'Chart.js', 26, '#1565c0')
text(draw, 985, 195, 'garis tren', 22, '#1e3a8a')
text(draw, 985, 270, 'Grafik tampil.', 18, '#1e3a8a')
text(draw, 985, 315, 'polling 5 detik', 16, '#1565c0')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Kemarin satu angka. Hari ini garis tren satu jam.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-45 · Chart.js dari CDN, histori SQLite', 26, '#ffffff')
text(draw, 600, 570, 'http://127.0.0.1:5000  ·  /history?hours=1  ·  bukan MySQL', 18, '#dbeafe')
save(image, 'fs45-cover-chart.jpg')
image.save(OUT / 'fs45-cover-chart.webp', 'WEBP', quality=85)
print('fs45-cover-chart.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-45 — lima langkah, jangan loncat', 'Browser dulu. File Explorer. Notepad. Isi histori lalu Flask. Baru buka grafik.')
steps = [
    ('1', 'Buka browser', 'artikel ini\njangan ketik dulu', '#fff8e1', '#f9a825'),
    ('2', 'File Explorer', 'folder fsiot-fs39\nstasiun.db ada', '#e3f2fd', '#1565c0'),
    ('3', 'Notepad', 'isi_histori.py\ndashboard.html', '#fff7ed', '#c2410c'),
    ('4', 'PowerShell', '12 titik siap\nlalu Flask', '#e8f5e9', '#2e7d32'),
    ('5', 'Browser', 'http://127.0.0.1\n:5000  grafik', '#f3e8ff', '#6d28d9'),
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
text(draw, 700, 575, 'Catatan lab: jangan MySQL, jangan file://, jangan ubah ExecutionPolicy, jangan tombol ON/OFF.', 17, '#b45309')
save(image, 'fs45-tools-order.png')

# Why a chart
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Satu angka kemarin. Hari ini garis tren satu jam', 'Baca kiri ke kanan: angka tunggal → histori 1 jam → garis Chart.js.')
box(draw, (70, 155, 460, 520), '#fff8e1', '#b45309')
text(draw, 265, 220, 'FS-44', 28, '#b45309')
text(draw, 265, 290, 'satu angka', 22)
text(draw, 265, 360, 'Suhu tampil.', 20, '#92400e')
text(draw, 265, 430, 'titik terakhir', 20, '#92400e')
box(draw, (500, 155, 900, 520), '#e8f5e9', '#166534')
text(draw, 700, 220, 'Histori', 28, '#166534')
text(draw, 700, 290, '12 titik', 22)
text(draw, 700, 360, 'rentang 1 jam', 20, '#14532d')
text(draw, 700, 430, 'SQLite cukup', 20, '#14532d')
box(draw, (940, 155, 1330, 520), '#e3f2fd', '#1565c0')
text(draw, 1135, 220, 'Grafik', 28, '#1565c0')
text(draw, 1135, 290, 'Chart.js', 22)
text(draw, 1135, 360, 'garis tren', 20, '#1e3a8a')
text(draw, 1135, 430, 'Grafik tampil.', 20, '#1e3a8a')
text(draw, 700, 585, 'Catatan lab: jangan menunggu MySQL. Gudang tetap stasiun.db. Tombol ON/OFF ditunda FS-46.', 17, '#b45309')
save(image, 'fs45-why-chart.png')

# History endpoint
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Pintu histori: 1 jam, paling banyak 60 titik', 'Baca kiri ke kanan: stasiun.db → /history?hours=1 → JSON baris terbatas.')
flow = [
    (50, 'Gudang', 'stasiun.db\nreceived_at', '#e8f5e9', '#166534'),
    (390, 'Saring', 'satu jam\nterakhir', '#fff8e1', '#b45309'),
    (730, 'Pangkas', 'maks 60 titik\nbukan semua baris', '#fff7ed', '#c2410c'),
    (1070, 'JSON', '/history\n?hours=1', '#e3f2fd', '#1565c0'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: terlalu banyak titik membuat garis ramai. Lab memotong di 60.', 17, '#b45309')
save(image, 'fs45-history.png')

# CDN safe step
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Chart.js dari CDN — langkah aman', 'HTTPS, versi dikunci, bukan zip acak, bukan npm hari ini.')
box(draw, (70, 155, 460, 520), '#e8f5e9', '#166534')
text(draw, 265, 220, 'Pakai', 28, '#166534')
text(draw, 265, 290, 'jsDelivr', 22)
text(draw, 265, 360, 'chart.js@4.4.1', 20, '#14532d')
text(draw, 265, 430, 'HTTPS saja', 20, '#14532d')
box(draw, (500, 155, 900, 520), '#fff8e1', '#b45309')
text(draw, 700, 220, 'Jangan', 28, '#b45309')
text(draw, 700, 290, 'zip tak dikenal', 22)
text(draw, 700, 360, 'http tanpa s', 20, '#92400e')
text(draw, 700, 430, 'npm / webpack', 20, '#92400e')
box(draw, (940, 155, 1330, 520), '#e3f2fd', '#1565c0')
text(draw, 1135, 220, 'Hasil', 28, '#1565c0')
text(draw, 1135, 290, 'script di head', 22)
text(draw, 1135, 360, 'lalu canvas', 20, '#1e3a8a')
text(draw, 1135, 430, 'satu origin Flask', 20, '#1e3a8a')
text(draw, 700, 585, 'Catatan lab: CDN hanya memuat Chart.js. JSON tetap dari 127.0.0.1:5000.', 17, '#b45309')
save(image, 'fs45-cdn.png')

# Flask serves chart
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Gambar utama — Flask menyajikan halaman dan histori', 'Baca kiri ke kanan: browser → / → dashboard.html → fetch /history → garis.')
nodes = [
    (40, 'Browser', 'ketik\n:5000', '#fff8e1', '#f9a825'),
    (310, 'GET /', 'dashboard\n.html', '#e3f2fd', '#1565c0'),
    (580, 'Flask', 'pintu_\nstasiun.py', '#fff7ed', '#c2410c'),
    (850, 'GET JSON', '/history\n?hours=1', '#f3e8ff', '#6d28d9'),
    (1120, 'Garis', 'Chart.js\nGrafik tampil.', '#e8f5e9', '#166534'),
]
for left, title, body, fill, color in nodes:
    box(draw, (left, 160, left + 240, 500), fill, color)
    text(draw, left + 120, 220, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 120, 330 + line_index * 48, line, 20, '#353535')
    if left < 1120:
        arrow(draw, (left + 240, 330), (left + 270, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: satu proses Flask. Jangan file://. Jangan dua port. Jangan flask-cors.', 16, '#b45309')
save(image, 'fs45-flask-serve.png')

# Polling
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Polling: halaman bertanya lagi setiap 5 detik', 'Baca kiri ke kanan: muat() → fetch /history → update garis → jeda 5 detik.')
flow = [
    (50, 'muat()', 'fungsi di\ndashboard.html', '#fff8e1', '#f9a825'),
    (390, 'fetch', '/history\n?hours=1', '#e3f2fd', '#1565c0'),
    (730, 'update', 'label jam\nangka suhu', '#fff7ed', '#c2410c'),
    (1070, 'jeda', '5 detik\nlalu ulang', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: grafik boleh diam jika tidak ada titik baru. Yang dikunci adalah teks Grafik tampil.', 16, '#b45309')
save(image, 'fs45-polling.png')

# Browser chart illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Browser sudah menampilkan garis tren suhu', 'Alamat: http://127.0.0.1:5000  — status Grafik tampil.')
box(draw, (80, 145, 1320, 650), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'http://127.0.0.1:5000', 24, '#1565c0')
box(draw, (130, 270, 1260, 610), '#f5f5f0', '#1f1f1f', 2)
text(draw, 280, 315, 'Stasiun meja', 24, '#1f1f1f')
text(draw, 280, 360, '27.5', 36, '#166534')
# simple line chart mock
points = [(220, 540), (360, 510), (500, 490), (640, 470), (780, 455), (920, 430), (1060, 410), (1180, 400)]
draw.line(points, fill='#1565c0', width=6)
for point in points:
    draw.ellipse((point[0] - 7, point[1] - 7, point[0] + 7, point[1] + 7), fill='#1565c0')
text(draw, 700, 575, 'Grafik tampil.', 22, '#166534')
text(draw, 700, 685, 'Catatan lab: ilustrasi meniru jendela browser. Angka dan bentuk garis boleh berbeda.', 16, '#b45309')
save(image, 'fs45-browser-chart.png')

# Time axis illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Sumbu waktu memakai jam, bukan millis', 'Label 08:15, 08:20, 08:25. Jangan pakai hitungan nyala ulang.')
box(draw, (80, 145, 680, 600), '#e8f5e9', '#166534')
text(draw, 380, 200, 'Pakai', 26, '#166534')
text(draw, 380, 270, 'received_at', 24)
text(draw, 380, 340, '08:15  08:20  08:25', 22, '#14532d')
text(draw, 380, 420, 'jam dinding', 22, '#14532d')
text(draw, 380, 500, 'satu arah ke kanan', 20, '#166534')
box(draw, (720, 145, 1320, 600), '#fff1f2', '#9a3412')
text(draw, 1020, 200, 'Jangan', 26, '#9a3412')
text(draw, 1020, 270, 'millis / counter', 24)
text(draw, 1020, 340, '0, 0, 0 lagi', 22, '#7c2d12')
text(draw, 1020, 420, 'grafik meloncat', 22, '#7c2d12')
text(draw, 1020, 500, 'setelah nyala ulang', 20, '#9a3412')
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru sumbu grafik. Bukan screenshot jendela resmi.', 16, '#b45309')
save(image, 'fs45-time-axis.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika grafik tidak muncul', 'Perbaiki isi histori, Flask, alamat http, lalu CDN. Jangan MySQL.')
checks = [
    (40, '1. Titik', 'isi_histori.py\nbelum 12 titik', '#fff8e1', '#f9a825'),
    (380, '2. Flask', 'terminal belum\nPintu stasiun', '#e3f2fd', '#1565c0'),
    (720, '3. Alamat', 'masih file://\nbukan :5000', '#fff7ed', '#c2410c'),
    (1060, '4. CDN', 'Chart.js gagal\njaringan putus', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: Flask ke 127.0.0.1:5000. Jangan buka port ke internet. Jangan tombol ON/OFF.', 17, '#b45309')
save(image, 'fs45-troubleshooting.png')
