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
text(draw, 215, 230, 'satu berkas', 20, '#475569')
text(draw, 215, 280, 'tetap jalur utama', 18, '#475569')
box(draw, (430, 70, 770, 350), '#fff8e1', '#ffffff', 4)
text(draw, 600, 125, 'Python', 26, '#b45309')
text(draw, 600, 185, 'salin_ke_mysql.py', 20)
text(draw, 600, 235, 'folder fsiot-fs39', 20, '#92400e')
text(draw, 600, 285, 'connector 26.7.0', 18, '#92400e')
box(draw, (810, 70, 1160, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 985, 125, 'MariaDB', 26, '#1565c0')
text(draw, 985, 185, '127.0.0.1:3306', 22, '#1e3a8a')
text(draw, 985, 235, 'database stasiun', 18, '#1e3a8a')
text(draw, 985, 285, '10 baris telemetry', 18, '#1e3a8a')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Modul opsional. SQLite tidak dihapus. Belum Flask.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-41 · Histori stasiun ke MariaDB', 26, '#ffffff')
text(draw, 600, 570, 'XAMPP  ·  phpMyAdmin  ·  salin 10 baris', 20, '#dbeafe')
save(image, 'fs41-cover-mysql.jpg')
image.save(OUT / 'fs41-cover-mysql.webp', 'WEBP', quality=85)
print('fs41-cover-mysql.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-41 — lima langkah, jangan loncat', 'Browser dulu. Control Panel MySQL Running. Script baru setelah pip.')
steps = [
    ('1', 'Buka browser', 'artikel ini\napachefriends.org', '#fff8e1', '#f9a825'),
    ('2', 'XAMPP', 'Control Panel\nStart MySQL', '#e3f2fd', '#1565c0'),
    ('3', 'phpMyAdmin', 'buat database\nstasiun', '#fff7ed', '#c2410c'),
    ('4', 'Notepad', 'requirements\nsalin_ke_mysql.py', '#e8f5e9', '#2e7d32'),
    ('5', 'PowerShell', 'pip lalu salin\n10 baris', '#f3e8ff', '#6d28d9'),
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
text(draw, 700, 575, 'Catatan lab: modul opsional. Jangan Flask, jangan hapus stasiun.db, jangan ubah ExecutionPolicy.', 17, '#b45309')
save(image, 'fs41-tools-order.png')

# Why MariaDB
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'SQLite tetap jalur utama. MariaDB adalah jalur B', 'Satu PC lab cukup SQLite. Server DB berguna jika banyak program membaca.')
box(draw, (70, 155, 660, 520), '#e8f5e9', '#166534')
text(draw, 365, 210, 'SQLite', 28, '#166534')
text(draw, 365, 280, 'satu berkas stasiun.db', 22)
text(draw, 365, 350, 'cukup untuk 1 PC', 20, '#14532d')
text(draw, 365, 420, 'FS-42 membaca ini', 20, '#14532d')
text(draw, 365, 480, 'jangan dihapus hari ini', 18, '#166534')
box(draw, (740, 155, 1330, 520), '#e3f2fd', '#1565c0')
text(draw, 1035, 210, 'MariaDB', 28, '#1565c0')
text(draw, 1035, 280, 'layanan di 3306', 22)
text(draw, 1035, 350, 'butuh user + sandi', 20, '#1e3a8a')
text(draw, 1035, 420, 'capstone tidak wajib', 20, '#1e3a8a')
text(draw, 1035, 480, 'jalur B, boleh dilewati', 18, '#1565c0')
text(draw, 700, 585, 'Catatan lab: ini bukan naik kelas wajib. FS-42 tetap memakai SQLite kecuali kamu pilih lain.', 17, '#b45309')
save(image, 'fs41-why-mysql.png')

# Download apachefriends
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Unduh XAMPP dari apachefriends.org', 'Baca kiri ke kanan: browser → situs resmi → pemasang Windows 64-bit')
flow = [
    (50, 'Browser', 'Chrome / Edge\nbuka situs', '#fff8e1', '#f9a825'),
    (390, 'Situs', 'apachefriends.org\nXAMPP Windows', '#e3f2fd', '#1565c0'),
    (730, 'Pemasang', '64-bit installer\nbukan Source', '#fff7ed', '#c2410c'),
    (1070, 'Selesai', 'ikon Control\nPanel di Start', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: kalau XAMPP atau Laragon sudah ada, jangan pasang yang kedua. Port 3306 hanya satu.', 17, '#b45309')
save(image, 'fs41-download.png')

# XAMPP Control Panel illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'XAMPP Control Panel: MySQL sudah Running', 'Apache boleh menyala agar phpMyAdmin terbuka. Tombol Start ada di baris modul.')
box(draw, (80, 145, 1320, 620), '#ffffff', '#1565c0')
box(draw, (110, 175, 1290, 245), '#eef6ff', '#90caf9', 3)
text(draw, 700, 210, 'XAMPP Control Panel', 24, '#1565c0')
rows = [
    ('Apache', 'Running', '#e8f5e9', '#166534', 'pid 4321  port 80,443'),
    ('MySQL', 'Running', '#e8f5e9', '#166534', 'pid 4402  port 3306'),
    ('FileZilla', 'Stopped', '#f8fafc', '#64748b', 'tidak dipakai hari ini'),
    ('Tomcat', 'Stopped', '#f8fafc', '#64748b', 'tidak dipakai hari ini'),
]
for index, (name, status, fill, color, extra) in enumerate(rows):
    top = 270 + index * 80
    box(draw, (130, top, 1260, top + 70), fill, color, 2)
    text(draw, 280, top + 35, name, 22, color)
    text(draw, 620, top + 35, status, 22, color)
    text(draw, 1000, top + 35, extra, 16, '#475569')
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru Control Panel XAMPP (Apache Friends, GPL). Bukan screenshot jendela resmi.', 16, '#b45309')
save(image, 'fs41-xampp.png')

# phpMyAdmin illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'phpMyAdmin sudah menampilkan database stasiun', 'Browser 127.0.0.1/phpmyadmin. Database baru, tabel masih kosong sebelum Python menyalin.')
box(draw, (80, 145, 430, 620), '#ffffff', '#1565c0')
text(draw, 255, 185, 'Databases', 20, '#1565c0')
box(draw, (110, 220, 400, 300), '#1565c0', '#1565c0', 2)
text(draw, 255, 260, 'stasiun', 22, '#ffffff')
box(draw, (110, 320, 400, 390), '#f8fafc', '#cbd5e1', 2)
text(draw, 255, 355, 'information_schema', 16, '#64748b')
box(draw, (110, 410, 400, 480), '#f8fafc', '#cbd5e1', 2)
text(draw, 255, 445, 'mysql', 16, '#64748b')
box(draw, (110, 500, 400, 570), '#fff8e1', '#f9a825', 2)
text(draw, 255, 535, 'New', 18, '#b45309')
box(draw, (460, 145, 1320, 620), '#ffffff', '#90caf9', 3)
text(draw, 890, 200, 'stasiun', 26, '#0f172a')
text(draw, 890, 270, 'Collation  utf8mb4_general_ci', 20, '#334155')
box(draw, (560, 330, 1220, 520), '#eef6ff', '#1565c0')
text(draw, 890, 390, 'No tables found in database.', 22, '#1565c0')
text(draw, 890, 450, 'Python akan membuat tabel telemetry', 18, '#1e3a8a')
text(draw, 700, 665, 'Catatan lab: ilustrasi meniru phpMyAdmin. Tampilan resmi phpMyAdmin tidak dipakai utuh.', 17, '#b45309')
save(image, 'fs41-phpmyadmin.png')

# pip + venv
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'venv yang sama, tambah satu baris terkunci', 'Baca kiri ke kanan: folder lab → venv FS-39 → pip -r → connector 26.7.0')
flow = [
    (50, 'Folder', 'Documents\\\nfsiot-fs39', '#fff8e1', '#f9a825'),
    (390, 'venv', '.venv dari\nFS-39', '#e3f2fd', '#1565c0'),
    (730, 'pip', 'python -m pip\ninstall -r', '#fff7ed', '#c2410c'),
    (1070, 'Terkunci', 'connector\n==26.7.0', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: pakai .venv\\Scripts\\python.exe. Jika Activate.ps1 ditolak, jangan ubah ExecutionPolicy.', 17, '#b45309')
save(image, 'fs41-pip-venv.png')

# Copy flow — main figure
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Gambar utama — salin histori, jangan hapus berkas', 'Baca dari kiri ke kanan: stasiun.db → Python → MariaDB → 10 baris')
nodes = [
    (40, 'SQLite', 'stasiun.db\ntetap ada', '#e8f5e9', '#166534'),
    (310, 'Script', 'salin_ke_\nmysql.py', '#fff8e1', '#f9a825'),
    (580, 'Koneksi', '127.0.0.1\nport 3306', '#fff7ed', '#c2410c'),
    (850, 'Tabel', 'stasiun\ntelemetry', '#f3e8ff', '#6d28d9'),
    (1120, 'Bukti', '10 baris\nSELECT', '#e3f2fd', '#1565c0'),
]
for left, title, body, fill, color in nodes:
    box(draw, (left, 160, left + 240, 500), fill, color)
    text(draw, left + 120, 220, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 120, 330 + line_index * 48, line, 20, '#353535')
    if left < 1120:
        arrow(draw, (left + 240, 330), (left + 270, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: menjalankan ulang mengosongkan tabel lab lalu menyalin lagi. stasiun.db tidak disentuh.', 17, '#b45309')
save(image, 'fs41-copy-flow.png')

# SELECT bukti
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Bukti berhasil — 10 baris di MariaDB', 'lihat_mysql.py membaca tabel telemetry. stasiun.db di folder lab tetap ada.')
box(draw, (70, 145, 1330, 530), '#ffffff', '#1565c0')
text(draw, 700, 185, 'python lihat_mysql.py', 22, '#1565c0')
text(draw, 700, 235, 'Jumlah baris: 10', 26, '#166534')
headers = ['id', 'device_id', 'temperature_c', 'humidity_pct']
values = [
    ('1', 'esp32-meja-01', '27.0', '60.0'),
    ('2', 'esp32-meja-01', '27.4', '61.0'),
    ('...', '...', '...', '...'),
    ('10', 'esp32-meja-01', '30.6', '69.0'),
]
for col, title in enumerate(headers):
    text(draw, 200 + col * 300, 300, title, 18, '#64748b')
for row_index, row in enumerate(values):
    for col, cell in enumerate(row):
        text(draw, 200 + col * 300, 360 + row_index * 38, cell, 18, '#1f1f1f')
text(draw, 700, 585, 'Catatan lab: angka suhu boleh berbeda. Yang dikunci adalah 10 baris terbaca dari MariaDB.', 17, '#b45309')
save(image, 'fs41-select.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika baris tidak muncul', 'Perbaiki layanan MySQL dan venv dulu. Jangan pip ke Python global.')
checks = [
    (40, '1. MySQL', 'Control Panel\nbelum Running', '#fff8e1', '#f9a825'),
    (380, '2. Sandi', 'root lab kosong\nAccess denied', '#e3f2fd', '#1565c0'),
    (720, '3. SQLite', 'stasiun.db\nbelum ada', '#fff7ed', '#c2410c'),
    (1060, '4. Port', '3306 dipakai\ninstal ganda', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: Python ke 127.0.0.1:3306. Jangan buka 3306 ke internet. Jangan ubah firewall rumah.', 17, '#b45309')
save(image, 'fs41-troubleshooting.png')
