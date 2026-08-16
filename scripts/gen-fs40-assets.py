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
image = Image.new('RGB', (1200, 675), '#0f3d2e')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#1b7a4a')
box(draw, (40, 70, 390, 350), '#e8f5e9', '#ffffff', 4)
text(draw, 215, 125, 'MQTT', 28, '#166534')
text(draw, 215, 180, '127.0.0.1:1883', 22)
text(draw, 215, 230, 'topic telemetry', 20, '#475569')
text(draw, 215, 280, 'paho-mqtt 2.1.0', 18, '#475569')
box(draw, (430, 70, 770, 350), '#fff8e1', '#ffffff', 4)
text(draw, 600, 125, 'Python', 26, '#b45309')
text(draw, 600, 185, 'callback pesan', 22)
text(draw, 600, 235, 'cetak di PowerShell', 20, '#92400e')
text(draw, 600, 285, 'folder fsiot-fs39', 18, '#92400e')
box(draw, (810, 70, 1160, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 985, 125, 'SQLite', 26, '#1565c0')
text(draw, 985, 185, 'stasiun.db', 22, '#1e3a8a')
text(draw, 985, 235, '10 baris telemetry', 18, '#1e3a8a')
text(draw, 985, 285, 'plus stasiun.csv', 18, '#1e3a8a')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Hari ini Python menyimpan. Node-RED tetap otak visual.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-40 · Subscriber MQTT ke SQLite', 26, '#ffffff')
text(draw, 600, 570, 'paho-mqtt  ·  CSV  ·  SQLite  ·  belum Flask', 20, '#dbeafe')
save(image, 'fs40-cover-sqlite.jpg')
image.save(OUT / 'fs40-cover-sqlite.webp', 'WEBP', quality=85)
print('fs40-cover-sqlite.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-40 — lima langkah, jangan loncat', 'Browser dulu. MQTTX disiapkan. Script baru dijalankan setelah pip.')
steps = [
    ('1', 'Buka browser', 'baca langkah\nsiapkan artikel', '#fff8e1', '#f9a825'),
    ('2', 'MQTTX', 'Connect\n127.0.0.1:1883', '#e3f2fd', '#1565c0'),
    ('3', 'Notepad', 'requirements.txt\nterima_stasiun.py', '#fff7ed', '#c2410c'),
    ('4', 'PowerShell', 'pip install -r\nlalu jalankan', '#e8f5e9', '#2e7d32'),
    ('5', 'Kirim pesan', 'satu di MQTTX\nlalu 10 contoh', '#f3e8ff', '#6d28d9'),
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
text(draw, 700, 575, 'Catatan lab: Mosquitto harus sudah terbuka. Jangan Flask, jangan MySQL, jangan ubah ExecutionPolicy.', 17, '#b45309')
save(image, 'fs40-tools-order.png')

# Why Python stores
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Python menyimpan. Node-RED tetap otak visual', 'Dua program PC, dua tugas. Jangan hapus alur FS-38.')
box(draw, (70, 155, 660, 520), '#fff7ed', '#c2410c')
text(draw, 365, 210, 'Node-RED', 28, '#c2410c')
text(draw, 365, 280, 'otak jika-maka', 22)
text(draw, 365, 350, 'ambang 30 di editor', 20, '#7c2d12')
text(draw, 365, 420, 'Deploy kanan atas', 20, '#7c2d12')
text(draw, 365, 480, 'tetap hidup hari ini', 18, '#9a3412')
box(draw, (740, 155, 1330, 520), '#e8f5e9', '#166534')
text(draw, 1035, 210, 'Python', 28, '#166534')
text(draw, 1035, 280, 'penerima + gudang', 22)
text(draw, 1035, 350, 'CSV lalu SQLite', 20, '#14532d')
text(draw, 1035, 420, '10 baris terbaca', 20, '#14532d')
text(draw, 1035, 480, 'pelengkap, bukan ganti', 18, '#166534')
text(draw, 700, 585, 'Catatan lab: ESP32 boleh menyala dari FS-38. Tidak ada kabel baru. Tidak ada Upload.', 18, '#b45309')
save(image, 'fs40-why-python.png')

# MQTTX illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'MQTTX sudah tersambung ke komputer ini', 'Host 127.0.0.1, port 1883. Topic telemetry siap. Belum tekan Publish.')
box(draw, (80, 145, 1320, 620), '#ffffff', '#1565c0')
box(draw, (110, 175, 430, 590), '#eef6ff', '#90caf9', 3)
text(draw, 270, 215, 'Connections', 20, '#1565c0')
box(draw, (130, 250, 410, 340), '#1565c0', '#1565c0', 2)
text(draw, 270, 280, 'FS40 simpan PC', 18, '#ffffff')
text(draw, 270, 310, 'Connected', 16, '#dbeafe')
box(draw, (130, 365, 410, 455), '#ffffff', '#90caf9', 2)
text(draw, 270, 395, 'FS35 perintah LAN', 16, '#475569')
text(draw, 270, 425, 'tidak dipakai hari ini', 14, '#64748b')
box(draw, (460, 175, 1290, 590), '#f8fafc', '#cbd5e1', 3)
text(draw, 875, 215, 'New Connection', 22, '#0f172a')
text(draw, 875, 275, 'Name   FS40 simpan PC', 20, '#334155')
text(draw, 875, 335, 'Host   127.0.0.1', 22, '#1565c0')
text(draw, 875, 395, 'Port   1883', 22, '#1565c0')
box(draw, (620, 445, 1130, 545), '#e8f5e9', '#166534')
text(draw, 875, 480, 'Connected', 24, '#166534')
text(draw, 875, 520, 'siap publish telemetry', 18, '#14532d')
text(draw, 700, 665, 'Catatan lab: Python di PC memakai 127.0.0.1, sama seperti Node-RED. Bukan IPv4 ESP32.', 17, '#b45309')
save(image, 'fs40-mqttx.png')

# pip + venv
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'venv yang sama, versi pustaka dikunci', 'Baca kiri ke kanan: folder lab → venv → pip -r → paho-mqtt 2.1.0')
flow = [
    (50, 'Folder', 'Documents\\\nfsiot-fs39', '#fff8e1', '#f9a825'),
    (390, 'venv', '.venv dari\nFS-39', '#e3f2fd', '#1565c0'),
    (730, 'pip', 'python -m pip\ninstall -r', '#fff7ed', '#c2410c'),
    (1070, 'Terkunci', 'paho-mqtt\n==2.1.0', '#e8f5e9', '#2e7d32'),
]
for left, title, body, fill, color in flow:
    box(draw, (left, 155, left + 280, 500), fill, color)
    text(draw, left + 140, 215, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 140, 320 + line_index * 48, line, 20, '#353535')
    if left < 1070:
        arrow(draw, (left + 280, 330), (left + 320, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: pakai .venv\\Scripts\\python.exe. Jika Activate.ps1 ditolak, jangan ubah ExecutionPolicy.', 17, '#b45309')
save(image, 'fs40-pip-venv.png')

# Callback flow — main figure
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Gambar utama — pesan masuk, lalu disimpan', 'Baca dari kiri ke kanan: broker → callback → cetak → CSV → SQLite')
nodes = [
    (40, 'Broker', 'Mosquitto\n1883', '#e3f2fd', '#1565c0'),
    (310, 'Callback', 'on_message\nJSON', '#fff8e1', '#f9a825'),
    (580, 'Cetak', 'PowerShell\nDiterima:', '#fff7ed', '#c2410c'),
    (850, 'CSV', 'stasiun.csv\nappend', '#f3e8ff', '#6d28d9'),
    (1120, 'SQLite', 'stasiun.db\nINSERT', '#e8f5e9', '#166534'),
]
for left, title, body, fill, color in nodes:
    box(draw, (left, 160, left + 240, 500), fill, color)
    text(draw, left + 120, 220, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 120, 330 + line_index * 48, line, 20, '#353535')
    if left < 1120:
        arrow(draw, (left + 240, 330), (left + 270, 330), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: script berhenti sendiri setelah 10 baris valid. Ctrl+C boleh lebih awal.', 18, '#b45309')
save(image, 'fs40-callback.png')

# Script run illustration
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'PowerShell sudah menampilkan MQTT tersambung', 'Jendela ini tetap terbuka. Pesan berikutnya muncul di bawahnya.')
box(draw, (90, 150, 1310, 600), '#0f172a', '#1f1f1f')
lines = [
    ('PS Documents\\fsiot-fs39>', '#94a3b8'),
    ('.\\.venv\\Scripts\\python.exe terima_stasiun.py', '#e2e8f0'),
    ('Menyambung ke 127.0.0.1 port 1883', '#cbd5e1'),
    ('MQTT tersambung.', '#86efac'),
    ('Berlangganan: kodingindonesia/fsiot/esp32-meja-01/telemetry', '#93c5fd'),
    ('Diterima: {"device_id":"esp32-meja-01","temperature_c":28.4}', '#fde68a'),
    ('Baris 1 / 10', '#86efac'),
]
for index, (line, color) in enumerate(lines):
    draw.text((130, 190 + index * 52), line, font=font(22, bold=False), fill=color)
text(draw, 700, 655, 'Catatan lab: ini jendela yang menunggu. Jangan ditutup sebelum 10 baris, atau tekan Ctrl+C.', 17, '#b45309')
save(image, 'fs40-script-run.png')

# SQLite 10 rows
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Bukti berhasil — 10 baris di SQLite', 'lihat_db.py membaca stasiun.db. File Explorer juga menampilkan stasiun.csv.')
box(draw, (70, 145, 1330, 530), '#ffffff', '#1565c0')
text(draw, 700, 185, 'python lihat_db.py', 22, '#1565c0')
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
text(draw, 700, 585, 'Catatan lab: angka suhu boleh berbeda. Yang dikunci adalah 10 baris terbaca, bukan nilai ruangan.', 17, '#b45309')
save(image, 'fs40-sqlite.png')

# Bonus rules
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Bonus — aturan Python pelengkap Node-RED', 'Jika suhu > 30, publish command. Jangan jalankan berbarengan kecuali membandingkan.')
box(draw, (60, 155, 500, 520), '#e3f2fd', '#1565c0')
text(draw, 280, 220, 'Telemetry', 24, '#1565c0')
text(draw, 280, 300, 'temperature_c', 20)
text(draw, 280, 370, 'misalnya 31.2', 22, '#1e3a8a')
text(draw, 280, 450, 'masuk callback', 18, '#475569')
box(draw, (540, 155, 860, 520), '#fff8e1', '#f9a825')
text(draw, 700, 220, 'If', 24, '#b45309')
text(draw, 700, 300, 'suhu > 30', 22)
text(draw, 700, 370, 'maka relay on', 20, '#92400e')
text(draw, 700, 450, 'jika tidak: off', 18, '#92400e')
box(draw, (900, 155, 1340, 520), '#fff7ed', '#c2410c')
text(draw, 1120, 220, 'Command', 24, '#c2410c')
text(draw, 1120, 300, '{"relay":"on"}', 20)
text(draw, 1120, 370, 'topic command', 20, '#7c2d12')
text(draw, 1120, 450, 'ESP32 patuh', 18, '#9a3412')
arrow(draw, (500, 340), (540, 340), '#1f1f1f', 6, 14)
arrow(draw, (860, 340), (900, 340), '#1f1f1f', 6, 14)
text(draw, 700, 585, 'Catatan lab: ini opsional. Bukti wajib hari ini tetap 10 baris SQLite. Node-RED tidak diganti.', 17, '#b45309')
save(image, 'fs40-rules-bonus.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika baris tidak bertambah', 'Perbaiki broker dan venv dulu. Jangan pip ke Python global.')
checks = [
    (40, '1. Broker', 'jendela Mosquitto\nbelum terbuka', '#fff8e1', '#f9a825'),
    (380, '2. Host', 'Python 127.0.0.1\nbukan IP ESP32', '#e3f2fd', '#1565c0'),
    (720, '3. venv', 'pip di luar .venv\npaho tidak ketemu', '#fff7ed', '#c2410c'),
    (1060, '4. Loop', 'script langsung\nkeluar, belum 10', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: MQTTX dan Python harus ke broker yang sama. Topic harus sama persis dengan FS-34.', 17, '#b45309')
save(image, 'fs40-troubleshooting.png')
