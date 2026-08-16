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
image = Image.new('RGB', (1200, 675), '#12315c')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#1d64b8')
box(draw, (40, 70, 390, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 215, 125, 'Browser', 28, '#1565c0')
text(draw, 215, 180, 'python.org', 22)
text(draw, 215, 230, 'unduh Windows', 20, '#475569')
text(draw, 215, 280, 'bukan Microsoft Store', 18, '#475569')
box(draw, (430, 70, 770, 350), '#fff8e1', '#ffffff', 4)
text(draw, 600, 125, 'Pemasang', 26, '#b45309')
text(draw, 600, 185, 'centang PATH', 22)
text(draw, 600, 235, 'Add python.exe', 20, '#92400e')
text(draw, 600, 285, 'lalu Next sampai Finish', 18, '#92400e')
box(draw, (810, 70, 1160, 350), '#ecfdf5', '#ffffff', 4)
text(draw, 985, 125, 'PowerShell', 26, '#166534')
text(draw, 985, 185, 'python --version', 22, '#14532d')
text(draw, 985, 235, 'script siap_stasiun.py', 18, '#14532d')
text(draw, 985, 285, 'Siap terima data stasiun', 16, '#166534')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Hari ini PC mendapat Python. ESP32 boleh dicabut.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-39 · Python dari nol, script pertama', 26, '#ffffff')
text(draw, 600, 570, '3.11+  ·  venv  ·  belum MQTT  ·  belum paho', 20, '#dbeafe')
save(image, 'fs39-cover-python.jpg')
image.save(OUT / 'fs39-cover-python.webp', 'WEBP', quality=85)
print('fs39-cover-python.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-39 — lima langkah, jangan loncat', 'Browser dulu ke python.org. PowerShell baru setelah pemasang selesai.')
steps = [
    ('1', 'Buka browser', 'baca langkah\ndan python.org', '#fff8e1', '#f9a825'),
    ('2', 'python.org', 'Windows installer\n3.11 atau lebih baru', '#e3f2fd', '#1565c0'),
    ('3', 'Centang PATH', 'Add python.exe\nto PATH', '#fff7ed', '#c2410c'),
    ('4', 'PowerShell', 'python --version\nlalu pip', '#e8f5e9', '#2e7d32'),
    ('5', 'Notepad', 'siap_stasiun.py\nlalu jalankan', '#f3e8ff', '#7e22ce'),
]
for index, (number, title, body, fill, color) in enumerate(steps):
    left = 16 + index * 277
    box(draw, (left, 165, left + 258, 510), fill, color)
    box(draw, (left + 14, 184, left + 76, 246), '#ffffff', color, 3)
    text(draw, left + 45, 215, number, 28, color)
    for line_index, line in enumerate(title.split('\n')):
        text(draw, left + 129, 292 + line_index * 28, line, 18)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 129, 380 + line_index * 34, line, 16, '#353535')
    if index < 4:
        arrow(draw, (left + 262, 338), (left + 273, 338), '#1f1f1f', 5, 12)
text(draw, 700, 585, 'Catatan lab: jangan pip install paho-mqtt hari ini. Itu FS-40.', 17, '#b45309')
save(image, 'fs39-tools-order.png')

# Why PC Python
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Python hidup di PC. ESP32 tetap perangkat.', 'Hari ini tidak ada sketch baru dan tidak ada kabel baru.')
box(draw, (60, 155, 660, 520), '#fff7ed', '#c2410c')
text(draw, 360, 220, 'Bukan hari ini', 26, '#c2410c')
text(draw, 360, 290, 'paho-mqtt / SQLite', 22)
text(draw, 360, 350, 'aturan suhu di Python', 20)
text(draw, 360, 410, 'Upload ulang ESP32', 18, '#9a3412')
box(draw, (740, 155, 1340, 520), '#ecfdf5', '#166534')
text(draw, 1040, 220, 'Hari ini di PC', 26, '#166534')
text(draw, 1040, 290, 'python --version', 22)
text(draw, 1040, 350, 'venv + script hello', 20, '#14532d')
text(draw, 1040, 410, 'Siap terima data stasiun', 18, '#14532d')
text(draw, 700, 585, 'Catatan lab: Node-RED FS-38 tetap boleh hidup. Python pelengkap, bukan pengganti.', 18, '#b45309')
save(image, 'fs39-why-pc.png')

# Download page illustration
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'python.org sudah menampilkan tombol unduhan Windows', 'Buka browser dulu. Jangan buka Microsoft Store.')
box(draw, (80, 145, 1320, 630), '#ffffff', '#e5e7eb', 0)
text(draw, 700, 185, 'python.org/downloads', 24, '#1565c0')
box(draw, (200, 230, 1200, 430), '#3776ab', '#1f4e79', 4)
text(draw, 700, 290, 'Download Python 3.12.x', 32, '#ffffff')
text(draw, 700, 350, 'Windows installer 64-bit', 22, '#dbeafe')
text(draw, 700, 490, 'Pilih berkas .exe resmi. Angka patch boleh berbeda.', 20, '#334155')
text(draw, 700, 545, 'Minimal 3.11. Jangan Microsoft Store.', 20, '#b45309')
text(draw, 700, 700, 'Ilustrasi buatan Koding Indonesia (FS-39), meniru halaman unduhan python.org. Tangkapan layar resmi tidak dipakai utuh.', 16, '#353535')
save(image, 'fs39-download.png')

# Installer PATH illustration
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Centang Add python.exe to PATH sebelum Next', 'Kotak ini mudah terlewat. Tanpanya, PowerShell tidak mengenal python.')
box(draw, (180, 145, 1220, 630), '#ffffff', '#64748b', 4)
text(draw, 700, 185, 'Python 3.12 Setup', 26, '#1e3a8a')
box(draw, (240, 230, 1160, 330), '#fff8e1', '#f9a825', 4)
text(draw, 700, 260, '[x]  Add python.exe to PATH', 28, '#b45309')
text(draw, 700, 300, 'wajib dicentang hari ini', 18, '#92400e')
box(draw, (240, 360, 1160, 500), '#f8fafc', '#cbd5e1', 3)
text(draw, 700, 400, 'Install Now     atau     Customize', 22, '#334155')
text(draw, 700, 455, 'Terima bawaan, klik Next sampai Finish.', 18, '#475569')
text(draw, 700, 575, 'Tutup PowerShell lama. Buka yang baru setelah Finish.', 18, '#b45309')
text(draw, 700, 700, 'Catatan lab: tanpa centang PATH, perintah python tidak dikenali. Jangan menebak dari foto wizard orang lain.', 16, '#b45309')
save(image, 'fs39-installer-path.png')

# PowerShell version illustration
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'PowerShell sudah menampilkan versi Python dan pip', 'Buka dulu PowerShell yang baru. Tempel perintah satu per satu.')
box(draw, (80, 145, 1320, 630), '#1e293b', '#0f172a', 0)
text(draw, 280, 190, 'Windows PowerShell', 20, '#93c5fd')
draw.line([(110, 220), (1290, 220)], fill='#334155', width=2)
lines = [
    ('PS C:\\Users\\kamu>', '#94a3b8'),
    ('python --version', '#e2e8f0'),
    ('Python 3.12.4', '#86efac'),
    ('PS C:\\Users\\kamu>', '#94a3b8'),
    ('python -m pip --version', '#e2e8f0'),
    ('pip 24.x from ...', '#86efac'),
]
for index, (line, color) in enumerate(lines):
    text(draw, 700, 270 + index * 52, line, 22, color)
text(draw, 700, 700, 'Ilustrasi buatan Koding Indonesia (FS-39), meniru jendela PowerShell. Angka patch boleh berbeda asal 3.11 atau lebih baru.', 16, '#353535')
save(image, 'fs39-version-ok.png')

# venv
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Baca dari kiri ke kanan: folder, venv, python di dalamnya', 'venv = kotak Python khusus lab ini. Jangan campur dengan Python lain.')
boxes = [
    (40, '1. Folder', 'Documents\\\nfsiot-fs39', '#fff8e1', '#f9a825'),
    (380, '2. venv', 'python -m venv\n.venv', '#e3f2fd', '#1565c0'),
    (720, '3. Pakai', '.venv\\Scripts\\\npython.exe', '#e8f5e9', '#2e7d32'),
    (1060, '4. Script', 'siap_stasiun.py\ndi folder itu', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in boxes:
    box(draw, (left, 150, left + 300, 560), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
for left in (340, 680, 1020):
    arrow(draw, (left, 355), (left + 40, 355), '#1f1f1f', 6, 14)
text(draw, 700, 640, 'Catatan lab: jika Activate.ps1 ditolak, jangan ubah ExecutionPolicy. Pakai .venv\\Scripts\\python.exe langsung.', 17, '#b45309')
save(image, 'fs39-venv.png')

# Script run
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Script mencetak Siap terima data stasiun', 'Tulis di Notepad. Simpan sebagai siap_stasiun.py. Lalu jalankan di PowerShell.')
box(draw, (50, 150, 680, 560), '#ffffff', '#1565c0')
text(draw, 365, 190, 'siap_stasiun.py', 24, '#1565c0')
text(draw, 365, 270, 'import sys', 20)
text(draw, 365, 330, 'print("Siap terima data stasiun")', 18)
text(draw, 365, 400, 'nama = sys.argv[1] ...', 18, '#475569')
text(draw, 365, 460, 'print("Nama stasiun:", nama)', 18)
box(draw, (720, 150, 1350, 560), '#ecfdf5', '#166534')
text(draw, 1035, 190, 'PowerShell', 24, '#166534')
text(draw, 1035, 270, 'python siap_stasiun.py', 20)
text(draw, 1035, 350, 'Siap terima data stasiun', 22, '#14532d')
text(draw, 1035, 420, 'Nama stasiun: stasiun-meja-01', 18, '#14532d')
text(draw, 1035, 490, 'ini bukti berhasil hari ini', 18, '#166534')
text(draw, 700, 640, 'Catatan lab: nama berkas harus .py. Jangan tersimpan sebagai siap_stasiun.py.txt.', 18, '#b45309')
save(image, 'fs39-script-run.png')

# argv
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Argumen = nama stasiun di belakang perintah', 'Tanpa argumen memakai bawaan. Dengan argumen, nama ikut tercetak.')
box(draw, (60, 155, 660, 520), '#e3f2fd', '#1565c0')
text(draw, 360, 220, 'Tanpa argumen', 26, '#1565c0')
text(draw, 360, 300, 'python siap_stasiun.py', 20)
text(draw, 360, 370, 'Nama stasiun:', 18)
text(draw, 360, 430, 'stasiun-meja-01', 22, '#1e3a8a')
box(draw, (740, 155, 1340, 520), '#fff8e1', '#f9a825')
text(draw, 1040, 220, 'Dengan argumen', 26, '#b45309')
text(draw, 1040, 300, 'python siap_stasiun.py esp32-meja-01', 18)
text(draw, 1040, 370, 'Nama stasiun:', 18)
text(draw, 1040, 430, 'esp32-meja-01', 22, '#92400e')
text(draw, 700, 585, 'Catatan lab: spasi setelah .py penting. Itu pemisah perintah dan nama stasiun.', 18, '#b45309')
save(image, 'fs39-argv.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Empat pemeriksaan jika python tidak dikenali', 'Perbaiki PATH dulu. Jangan pip install acak.')
checks = [
    (40, '1. Store', 'jendela Microsoft\nStore terbuka', '#fff8e1', '#f9a825'),
    (380, '2. PATH', 'lupa centang\nAdd python.exe', '#e3f2fd', '#1565c0'),
    (720, '3. Jendela', 'PowerShell lama\nbelum ditutup', '#fff7ed', '#c2410c'),
    (1060, '4. pip', 'ketik pip saja\npakai python -m pip', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in checks:
    box(draw, (left, 150, left + 300, 500), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
text(draw, 700, 575, 'Catatan lab: jika Store terbuka, tutup. Unduh ulang dari python.org, centang PATH, buka PowerShell baru.', 17, '#b45309')
save(image, 'fs39-troubleshooting.png')
