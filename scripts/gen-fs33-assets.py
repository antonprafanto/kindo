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
    text(draw, width / 2, 57, title, 40)
    text(draw, width / 2, 96, subtitle, 22, '#404040')


def arrow(draw, start, end, fill, width=8, head=22):
    draw.line([start, end], fill=fill, width=width)
    ang = atan2(end[1] - start[1], end[0] - start[0])
    p2 = (end[0] - head * cos(ang - 0.4), end[1] - head * sin(ang - 0.4))
    p3 = (end[0] - head * cos(ang + 0.4), end[1] - head * sin(ang + 0.4))
    draw.polygon([end, p2, p3], fill=fill)


def save(image, name):
    image.save(OUT / name, optimize=True)
    print(name, image.size)


# Cover — Mosquitto visibly in the middle, arrows with heads
image = Image.new('RGB', (1200, 675), '#12315c')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#1d64b8')
box(draw, (70, 90, 360, 330), '#e3f2fd', '#ffffff', 4)
text(draw, 215, 165, 'MQTTX', 34, '#1565c0')
text(draw, 215, 220, 'klien', 24)
text(draw, 215, 265, 'kirim pesan', 22)
box(draw, (455, 95, 745, 325), '#1565c0', '#ffffff', 4)
text(draw, 600, 165, 'MOSQUITTO', 32, '#ffffff')
text(draw, 600, 220, 'broker lokal', 24, '#e3f2fd')
text(draw, 600, 265, 'kantor pos', 22, '#fff8e1')
box(draw, (840, 90, 1130, 330), '#e8f5e9', '#ffffff', 4)
text(draw, 985, 165, 'MQTTX', 34, '#2e7d32')
text(draw, 985, 220, 'klien', 24)
text(draw, 985, 265, 'lihat pesan', 22)
arrow(draw, (360, 210), (455, 210), '#ffd54f', 8, 20)
arrow(draw, (745, 210), (840, 210), '#ffd54f', 8, 20)
text(draw, 600, 430, '127.0.0.1 : 1883', 36, '#fff3b0')
text(draw, 600, 500, 'FS-33 · Broker MQTT di komputer sendiri', 32, '#ffffff')
text(draw, 600, 560, 'Hari ini: pasang Mosquitto, baru Connect di MQTTX', 22, '#dbeafe')
save(image, 'fs33-cover-mosquitto.jpg')
image.save(OUT / 'fs33-cover-mosquitto.webp', 'WEBP', quality=85)
print('fs33-cover-mosquitto.webp', image.size)

# Tools order — numbered 1–5, matching the article step cards
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-33 — lima langkah, jangan loncat', 'Satu komputer saja sudah cukup; ESP32 menyusul pada FS-34')
steps = [
    ('1', 'Buka browser', 'Chrome / Edge\nFirefox / Safari', '#fff8e1', '#f9a825'),
    ('2', 'Halaman unduhan', 'mosquitto.org\n/download', '#e3f2fd', '#1565c0'),
    ('3', 'Pasang Mosquitto', 'berkas x64.exe\ntanpa ubah opsi', '#fff3e0', '#ef6c00'),
    ('4', 'Buka PowerShell', 'jalankan broker\nbiarkan terbuka', '#e8f5e9', '#2e7d32'),
    ('5', 'Buka MQTTX', 'Connect boleh\nHost 127.0.0.1', '#fce4ec', '#c62828'),
]
for index, (number, title, body, fill, color) in enumerate(steps):
    left = 16 + index * 277
    box(draw, (left, 165, left + 258, 510), fill, color)
    box(draw, (left + 14, 184, left + 76, 246), '#ffffff', color, 3)
    text(draw, left + 45, 215, number, 28, color)
    text(draw, left + 129, 300, title, 18)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 129, 365 + line_index * 36, line, 16, '#353535')
    if index < 4:
        arrow(draw, (left + 262, 338), (left + 273, 338), '#1f1f1f', 5, 12)
text(draw, 700, 585, 'Belum perlu: ESP32 · Arduino IDE · IP router · firewall · broker publik', 22, '#353535')
save(image, 'fs33-tools-order.png')

# Local boundary with two-way arrows
image = Image.new('RGB', (1200, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Lab ini hanya berputar di komputer sendiri', '127.0.0.1 berarti "komputer ini"; pesan tidak keluar ke internet')
draw.rounded_rectangle((95, 165, 1105, 515), radius=28, fill='#e3f2fd', outline='#1565c0', width=5)
text(draw, 600, 205, 'KOMPUTER KAMU', 27, '#1565c0')
box(draw, (160, 260, 450, 470), '#ffffff', '#2e7d32')
text(draw, 305, 320, 'MQTTX', 33, '#2e7d32')
text(draw, 305, 375, 'klien', 24)
text(draw, 305, 420, 'kirim + lihat', 22)
box(draw, (750, 250, 1040, 480), '#e8f5e9', '#2e7d32')
text(draw, 895, 320, 'Mosquitto', 32, '#2e7d32')
text(draw, 895, 375, 'broker', 24)
text(draw, 895, 420, 'port 1883', 22)
arrow(draw, (450, 330), (740, 330), '#1565c0', 8, 18)
arrow(draw, (740, 400), (450, 400), '#2e7d32', 8, 18)
text(draw, 595, 318, 'publish', 16, '#1565c0')
text(draw, 595, 428, 'subscribe', 16, '#2e7d32')
text(draw, 600, 575, 'Jangan buka port router atau firewall untuk praktik satu komputer ini.', 23, '#b91c1c')
save(image, 'fs33-local-only.png')

# MQTTX local connected illustration (do not reuse official preview: it shows a public broker)
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Sekarang Connect boleh — isi Host komputer ini', 'Jangan salin alamat broker dari screenshot internet')
box(draw, (50, 150, 1350, 620), '#1f2937', '#111827', 4)
box(draw, (50, 150, 128, 620), '#111827', '#111827', 0)
text(draw, 89, 210, 'X', 36, '#34d399')
text(draw, 89, 280, '+', 28, '#9ca3af')
box(draw, (128, 150, 430, 620), '#1f2937', '#1f2937', 0)
text(draw, 279, 200, 'Koneksi', 24, '#e5e7eb')
box(draw, (150, 240, 410, 360), '#065f46', '#34d399', 3)
text(draw, 280, 275, 'FS33 broker lokal', 18, '#ecfdf5')
text(draw, 280, 315, 'tersambung', 18, '#a7f3d0')
box(draw, (430, 150, 1350, 620), '#f8fafc', '#e5e7eb', 0)
text(draw, 890, 230, 'Host  127.0.0.1', 32, '#166534')
text(draw, 890, 290, 'Port  1883', 32, '#166534')
box(draw, (640, 350, 1140, 455), '#e8f5e9', '#2e7d32', 4)
text(draw, 890, 402, 'Bukan broker publik. Bukan IP router.', 20, '#14532d')
text(draw, 890, 530, 'Username dan sandi dikosongkan untuk lab satu komputer.', 18, '#4b5563')
text(draw, 700, 690, 'Ilustrasi jendela MQTTX buatan Koding Indonesia (FS-33), meniru tata letak aplikasi resmi EMQ.', 20, '#353535')
save(image, 'fs33-mqttx-local.png')

# First message — left to right
image = Image.new('RGB', (1200, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Pesan pertama: kirim dulu, broker teruskan, lalu terlihat', 'Satu jendela MQTTX boleh subscribe dan publish sekaligus')
box(draw, (50, 200, 340, 470), '#fff8e1', '#f9a825')
text(draw, 195, 265, 'MQTTX', 34, '#b56d00')
text(draw, 195, 325, '1. publish', 24)
text(draw, 195, 365, 'halo dari', 18)
text(draw, 195, 400, 'PC saya', 18)
text(draw, 195, 420, 'topic chat', 20)
box(draw, (445, 175, 755, 495), '#e8f5e9', '#2e7d32')
text(draw, 600, 250, 'Mosquitto', 34, '#2e7d32')
text(draw, 600, 315, '2. terima', 24)
text(draw, 600, 365, 'lalu teruskan', 24)
text(draw, 600, 420, '127.0.0.1:1883', 20)
box(draw, (860, 200, 1150, 470), '#e3f2fd', '#1565c0')
text(draw, 1005, 265, 'MQTTX', 34, '#1565c0')
text(draw, 1005, 325, '3. subscribe', 24)
text(draw, 1005, 375, 'pesan terlihat', 20)
text(draw, 1005, 420, 'topic yang sama', 20)
arrow(draw, (340, 335), (445, 335), '#f9a825', 8, 18)
arrow(draw, (755, 335), (860, 335), '#1565c0', 8, 18)
text(draw, 600, 560, 'Bukti sukses: teks yang kamu kirim muncul lagi di daftar pesan MQTTX.', 22, '#353535')
save(image, 'fs33-first-message.png')

# Troubleshooting
image = Image.new('RGB', (1200, 620), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Jika tidak tersambung, cek tiga hal ini', 'Mulai dari jendela broker, lalu alamat, baru port')
items = [
    ('1', 'Jendela broker', 'masih terbuka\ndan terlihat 1883', '#fff8e1', '#f9a825'),
    ('2', 'Alamat', 'pakai 127.0.0.1\nbukan IP router', '#e3f2fd', '#1565c0'),
    ('3', 'Port', 'isi 1883\ntanpa http://', '#e8f5e9', '#2e7d32'),
]
for index, (number, title, body, fill, color) in enumerate(items):
    left = 85 + index * 370
    box(draw, (left, 175, left + 300, 475), fill, color)
    box(draw, (left + 18, 195, left + 88, 265), '#ffffff', color, 3)
    text(draw, left + 53, 230, number, 30, color)
    text(draw, left + 150, 300, title, 27)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 365 + line_index * 38, line, 22, '#353535')
    if index < 2:
        arrow(draw, (left + 308, 325), (left + 362, 325), '#1f1f1f', 6, 14)
text(draw, 600, 555, 'Untuk lab lokal, jangan mengubah firewall atau konfigurasi listener.', 23, '#b91c1c')
save(image, 'fs33-troubleshooting.png')

# Official Mosquitto download page + Indonesian callouts
raw_candidates = [
    Path(r'c:\Users\anton\AppData\Local\Temp\cursor\screenshots\mosquitto-downloads-raw.png'),
    OUT / 'fs33-mosquitto-downloads-raw.png',
]
raw_path = next((path for path in raw_candidates if path.is_file()), None)
if raw_path is None:
    raise FileNotFoundError('Mosquitto downloads screenshot not found')
raw = Image.open(raw_path).convert('RGB')
if raw_path != OUT / 'fs33-mosquitto-downloads-raw.png':
    raw.save(OUT / 'fs33-mosquitto-downloads-raw.png')
rw, rh = raw.size
# Crop to Binary + Windows so x64.exe is readable (full-page crop looked like a broken Source section).
raw = raw.crop((0, int(rh * 0.28), rw, int(rh * 0.70)))
target_w = 1400
raw = raw.resize((target_w, int(raw.height * target_w / raw.width)), Image.Resampling.LANCZOS)
banner_h, foot_h = 96, 96
download = Image.new('RGB', (target_w, raw.height + banner_h + foot_h), '#f5f5f0')
download.paste(raw, (0, banner_h))
dwn = ImageDraw.Draw(download)
box(dwn, (22, 12, target_w - 22, banner_h - 8), '#e8f5e9', '#2e7d32')
text(dwn, target_w / 2, 38, 'Buka halaman ini: mosquitto.org/download', 26, '#14532d')
text(dwn, target_w / 2, 70, 'Klik berkas x64.exe di bagian Windows. Jangan unduh berkas Source (.tar.gz).', 18, '#1b4332')
box(dwn, (22, banner_h + raw.height + 8, target_w - 22, banner_h + raw.height + foot_h - 10), '#ffffff')
text(dwn, target_w / 2, banner_h + raw.height + 38, 'Sumber: https://mosquitto.org/download/ — Eclipse Mosquitto / Eclipse Foundation. Tangkapan layar 13 Agustus 2026.', 16)
text(dwn, target_w / 2, banner_h + raw.height + 68, 'Perangkat lunak Mosquitto berlisensi Eclipse Public License 2.0 dan Eclipse Distribution License 1.0.', 16, '#353535')
download.save(OUT / 'fs33-mosquitto-downloads.png', optimize=True)
print('fs33-mosquitto-downloads.png', download.size)
