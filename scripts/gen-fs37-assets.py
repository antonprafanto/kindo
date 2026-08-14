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


# Cover — store on card while Wi-Fi is down, then MQTTX fills
image = Image.new('RGB', (1200, 675), '#12315c')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#1d64b8')
box(draw, (40, 70, 390, 350), '#e3f2fd', '#ffffff', 4)
text(draw, 215, 125, 'ESP32 + microSD', 26, '#1565c0')
text(draw, 215, 180, 'pending.csv', 22)
text(draw, 215, 225, 'antrian belum terkirim', 18, '#475569')
text(draw, 215, 275, 'CS 5 · DHT22 GPIO 4', 18, '#475569')
box(draw, (430, 70, 770, 350), '#fff3e0', '#ffffff', 4)
text(draw, 600, 125, 'Wi-Fi putus', 28, '#c2410c')
text(draw, 600, 185, 'hotspot HP mati', 20)
text(draw, 600, 230, 'broker PC tetap hidup', 18, '#9a3412')
text(draw, 600, 280, 'data ke kartu, bukan RAM', 18, '#9a3412')
box(draw, (810, 70, 1160, 350), '#ecfdf5', '#ffffff', 4)
text(draw, 985, 125, 'MQTTX', 28, '#166534')
text(draw, 985, 185, 'nyala lagi', 22, '#14532d')
text(draw, 985, 230, 'from_sd: true', 20, '#166534')
text(draw, 985, 280, 'jendela waktu terisi', 18, '#166534')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Hari ini MQTT kembali. Kartu menahan data saat hotspot putus.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-37 · Kirim ulang dari microSD saat Wi-Fi kembali', 26, '#ffffff')
text(draw, 600, 570, 'Antrian di pending.csv  ·  bukan tumpukan RAM tak terbatas', 20, '#dbeafe')
save(image, 'fs37-cover-forward.jpg')
image.save(OUT / 'fs37-cover-forward.webp', 'WEBP', quality=85)
print('fs37-cover-forward.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-37 — lima langkah, jangan loncat', 'MQTTX dulu, baru Upload. Demo: buka panel HP, matikan hotspot — bukan router rumah.')
steps = [
    ('1', 'Buka browser', 'baca langkah\ndan sumber resmi', '#fff8e1', '#f9a825'),
    ('2', 'MQTTX + broker', 'Host = IPv4 PC\nport 1883', '#e3f2fd', '#1565c0'),
    ('3', 'Arduino IDE', 'SD.h core +\nArduinoMqttClient', '#e8f5e9', '#2e7d32'),
    ('4', 'Kabel SPI', 'sama seperti\nFS-36', '#e0f2f1', '#00897b'),
    ('5', 'Serial Monitor', 'lalu buka panel HP\nmatikan hotspot', '#f3e8ff', '#7e22ce'),
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
text(draw, 700, 585, 'Catatan lab: jangan matikan router rumah — Mosquitto di PC ikut mati. Matikan hotspot HP yang dipakai ESP32.', 17, '#b45309')
save(image, 'fs37-tools-order.png')

# Offline vs online
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Baca dari kiri ke kanan: putus, simpan, nyambung, kirim ulang', 'Jendela waktu demo tidak bolong total')
box(draw, (40, 150, 360, 560), '#fff7ed', '#c2410c')
text(draw, 200, 210, '1. Putus', 28, '#c2410c')
text(draw, 200, 280, 'hotspot HP mati', 20)
text(draw, 200, 340, 'MQTTX sepi', 20)
text(draw, 200, 400, 'ESP32 tetap nyala', 18, '#9a3412')
text(draw, 200, 460, 'USB tetap terpasang', 18, '#9a3412')
box(draw, (380, 150, 700, 560), '#e0f2f1', '#0f766e')
text(draw, 540, 210, '2. Simpan', 28, '#0f766e')
text(draw, 540, 280, 'pending.csv', 22)
text(draw, 540, 340, '5123,27.4', 20, '#134e4a')
text(draw, 540, 400, 'baris belum terkirim', 18, '#134e4a')
text(draw, 540, 460, 'bukan array RAM', 18, '#134e4a')
box(draw, (720, 150, 1040, 560), '#e3f2fd', '#1565c0')
text(draw, 880, 210, '3. Nyambung', 28, '#1565c0')
text(draw, 880, 280, 'hotspot nyala', 20)
text(draw, 880, 340, 'MQTT tersambung', 20)
text(draw, 880, 400, 'baca pending.csv', 18, '#1e3a8a')
text(draw, 880, 460, 'kirim satu per satu', 18, '#1e3a8a')
box(draw, (1060, 150, 1360, 560), '#ecfdf5', '#166534')
text(draw, 1210, 210, '4. Terisi', 28, '#166534')
text(draw, 1210, 280, 'MQTTX', 22)
text(draw, 1210, 340, 'from_sd: true', 20, '#14532d')
text(draw, 1210, 400, 'beberapa pesan', 18, '#14532d')
text(draw, 1210, 460, 'hampir bersamaan', 18, '#14532d')
arrow(draw, (360, 355), (380, 355), '#1f1f1f', 6, 14)
arrow(draw, (700, 355), (720, 355), '#1f1f1f', 6, 14)
arrow(draw, (1040, 355), (1060, 355), '#1f1f1f', 6, 14)
text(draw, 700, 640, 'Catatan lab: PC + Mosquitto tetap di Wi-Fi rumah. Yang dimatikan hanya hotspot HP ESP32.', 18, '#b45309')
save(image, 'fs37-offline-online.png')

# RAM vs SD
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'RAM kecil. Kartu yang menahan antrian.', 'Jangan menumpuk ratusan sampel di memori sampai ESP32 mentok')
box(draw, (60, 155, 660, 520), '#fff7ed', '#c2410c')
text(draw, 360, 220, 'Kalau ditumpuk di RAM', 26, '#c2410c')
text(draw, 360, 290, 'cepat penuh', 22)
text(draw, 360, 350, 'reset = data hilang', 22)
text(draw, 360, 410, 'lab ini tidak memakai cara ini', 18, '#9a3412')
box(draw, (740, 155, 1340, 520), '#ecfdf5', '#166534')
text(draw, 1040, 220, 'Antrian di pending.csv', 26, '#166534')
text(draw, 1040, 290, 'tahan puluhan baris', 22)
text(draw, 1040, 350, 'tetap ada setelah Wi-Fi putus', 20, '#14532d')
text(draw, 1040, 410, 'RAM hanya sampel yang sedang dipegang', 18, '#14532d')
text(draw, 700, 585, 'Catatan lab: satu sampel di variabel, sisanya di kartu. Itu store-and-forward untuk pemula.', 18, '#b45309')
save(image, 'fs37-ram-vs-sd.png')

# pending.csv vs live MQTT
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Dua berkas: pending.csv antrian, log.csv arsip', 'pending.csv dikosongkan setelah berhasil dikirim ulang')
box(draw, (50, 150, 670, 560), '#fff8e1', '#f9a825')
text(draw, 360, 210, 'pending.csv', 30, '#b45309')
text(draw, 360, 280, 'hanya yang belum terkirim', 20)
text(draw, 360, 350, '5123,27.4', 22, '#78350f')
text(draw, 360, 400, '10124,27.5', 22, '#78350f')
text(draw, 360, 470, 'tanda: belum ke broker', 18, '#92400e')
box(draw, (730, 150, 1350, 560), '#e3f2fd', '#1565c0')
text(draw, 1040, 210, 'log.csv', 30, '#1565c0')
text(draw, 1040, 280, 'arsip semua sampel', 20)
text(draw, 1040, 350, 'timestamp_ms,temperature_c,jalur', 18, '#1e3a8a')
text(draw, 1040, 410, 'kartu  atau  mqtt', 20, '#1e3a8a')
text(draw, 1040, 470, 'boleh dibuka di Notepad', 18, '#1e3a8a')
text(draw, 700, 640, 'Catatan lab: JSON ke MQTTX memakai from_sd true hanya saat kirim ulang dari kartu.', 18, '#b45309')
save(image, 'fs37-pending-csv.png')

# Serial Monitor
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Buka Tools → Serial Monitor, baud 115200', 'Cari Kartu siap, lalu Wi-Fi putus, lalu Kirim ulang dari kartu')
box(draw, (50, 145, 1350, 630), '#ffffff', '#1f2937', 4)
box(draw, (50, 145, 1350, 205), '#e2e8f0', '#cbd5e1', 0)
text(draw, 280, 175, 'Serial Monitor', 24, '#0f172a')
box(draw, (1080, 155, 1310, 195), '#166534', '#166534', 0)
text(draw, 1195, 175, '115200 baud', 16, '#ecfdf5')
lines = [
    ('#ecfdf5', '#166534', 'Kartu siap. Antrian di /pending.csv'),
    ('#e0f2f1', '#0f766e', 'Antrian hanya di kartu, bukan RAM tak terbatas.'),
    ('#dbeafe', '#1d4ed8', 'MQTT tersambung. Mengirim antrian kartu.'),
    ('#ecfdf5', '#166534', 'Terkirim: {"from_sd":false,"temperature_c":27.4}'),
    ('#fff7ed', '#c2410c', 'Wi-Fi putus. Disimpan ke pending.csv: 15123,27.6'),
    ('#ecfdf5', '#166534', 'Kirim ulang dari kartu: {"from_sd":true,"temperature_c":27.6}'),
]
for index, (fill, color, line) in enumerate(lines):
    top = 230 + index * 62
    box(draw, (80, top, 1320, top + 52), fill, color, 3)
    text(draw, 700, top + 26, line, 18, color)
text(draw, 700, 700, 'Ilustrasi buatan Koding Indonesia (FS-37), meniru Serial Monitor Arduino IDE 2. Menu: Tools → Serial Monitor.', 16, '#353535')
save(image, 'fs37-serial-monitor.png')

# MQTTX backfill
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'MQTTX sudah menampilkan kiriman ulang dari kartu', 'Host = IPv4 PC · Port 1883. Beberapa pesan from_sd true datang berdekatan.')
box(draw, (50, 145, 430, 630), '#eef2ff', '#cbd5e1', 0)
text(draw, 240, 190, 'MQTTX', 28, '#0f766e')
text(draw, 240, 230, 'Koneksi', 20, '#334155')
box(draw, (80, 265, 400, 385), '#ecfdf5', '#34d399', 4)
text(draw, 240, 300, 'FS37 store-forward', 18, '#14532d')
text(draw, 240, 340, 'tersambung', 18, '#166534')
box(draw, (430, 145, 1350, 630), '#ffffff', '#e5e7eb', 0)
text(draw, 890, 185, 'Host  192.168.1.23', 26, '#166534')
text(draw, 890, 230, 'Port  1883', 26, '#166534')
text(draw, 890, 270, 'kodingindonesia/fsiot/esp32-meja-01/telemetry', 16, '#334155')
box(draw, (470, 300, 1310, 390), '#ecfdf5', '#34d399', 3)
text(draw, 890, 345, '{"from_sd":false,"temperature_c":27.4}   live', 18, '#14532d')
box(draw, (470, 410, 1310, 500), '#fff7ed', '#f59e0b', 3)
text(draw, 890, 455, '{"from_sd":true,"temperature_c":27.6}   dari kartu', 18, '#9a3412')
box(draw, (470, 520, 1310, 600), '#fff7ed', '#f59e0b', 3)
text(draw, 890, 560, '{"from_sd":true,"temperature_c":27.5}   dari kartu', 18, '#9a3412')
text(draw, 700, 700, 'Ilustrasi MQTTX buatan Koding Indonesia (FS-37), meniru tata letak EMQ (Apache 2.0). 192.168.1.23 hanya contoh.', 16, '#353535')
save(image, 'fs37-mqttx-backfill.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Jika antrian tidak terkirim ulang, cek dari yang paling dekat', 'Jangan mengganti pin CS. Empat titik ini dulu.')
checks = [
    ('1', 'Hotspot', 'yang mati hanya HP\nPC tetap di rumah', '#fff8e1', '#f9a825'),
    ('2', 'Broker', 'jendela Mosquitto\n1883 masih terbuka', '#e3f2fd', '#1565c0'),
    ('3', 'Kartu', 'pending.csv\nCS GPIO 5 · FAT32', '#e8f5e9', '#2e7d32'),
    ('4', 'MQTTX', 'Host = IPv4 PC\nfrom_sd true', '#f3e8ff', '#7e22ce'),
]
for index, (number, title, body, fill, color) in enumerate(checks):
    left = 40 + index * 345
    box(draw, (left, 165, left + 300, 500), fill, color)
    box(draw, (left + 18, 185, left + 88, 255), '#ffffff', color, 3)
    text(draw, left + 53, 220, number, 28, color)
    text(draw, left + 150, 310, title, 28)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 375 + line_index * 38, line, 20, '#353535')
    if index < 3:
        arrow(draw, (left + 308, 332), (left + 337, 332), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: matikan router rumah = Mosquitto ikut mati. Itu bukan demo store-and-forward.', 18, '#b45309')
save(image, 'fs37-troubleshooting.png')

# Two Wi-Fi paths — do not reuse FS-34 "same Wi-Fi" diagram
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Dua Wi-Fi: PC di rumah, ESP32 di hotspot HP', 'MQTT_HOST = IPv4 PC. Bukan 127.0.0.1, bukan mematikan router.')
box(draw, (50, 150, 670, 560), '#ecfdf5', '#166534')
text(draw, 360, 200, 'PC / laptop', 28, '#166534')
text(draw, 360, 260, 'Wi-Fi rumah tetap nyala', 22)
text(draw, 360, 320, 'Mosquitto + MQTTX', 22)
text(draw, 360, 380, 'contoh IPv4 192.168.1.23', 20, '#14532d')
text(draw, 360, 440, 'dari ipconfig, bukan 127.0.0.1', 18, '#14532d')
text(draw, 360, 500, 'jangan matikan router ini', 18, '#b45309')
box(draw, (730, 150, 1350, 560), '#e0f2f1', '#0f766e')
text(draw, 1040, 200, 'ESP32 + kartu', 28, '#0f766e')
text(draw, 1040, 260, 'hotspot HP', 22)
text(draw, 1040, 320, 'SSID di sketch', 20)
text(draw, 1040, 380, 'MQTT_HOST = IPv4 PC', 20, '#134e4a')
text(draw, 1040, 440, 'USB tetap terpasang', 18, '#134e4a')
text(draw, 1040, 500, 'yang dimatikan hanya hotspot', 18, '#134e4a')
arrow(draw, (670, 355), (730, 355), '#1f1f1f', 8, 18)
text(draw, 700, 640, 'Catatan lab: 127.0.0.1 di ESP32 = ESP32 itu sendiri. Ganti 192.168.1.23 dengan IPv4 PC milikmu.', 18, '#b45309')
save(image, 'fs37-two-wifi.png')

# Phone hotspot demo — tools-first
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Demo putus: buka dulu panel HP, bukan router rumah', 'USB ESP32 tetap colok. Jendela Mosquitto di PC tetap terbuka.')
steps = [
    ('1', 'Geser dari atas\nlayar HP', 'atau buka\nPengaturan', '#fff8e1', '#f9a825'),
    ('2', 'Ketuk Hotspot', 'mati 20–40 detik', '#fff7ed', '#c2410c'),
    ('3', 'PC jangan disentuh', 'Mosquitto 1883\nmasih terbuka', '#e3f2fd', '#1565c0'),
    ('4', 'Nyalakan lagi', 'cari Kirim ulang\ndari kartu', '#ecfdf5', '#166534'),
]
for index, (number, title, body, fill, color) in enumerate(steps):
    left = 40 + index * 345
    box(draw, (left, 165, left + 300, 520), fill, color)
    box(draw, (left + 18, 185, left + 88, 255), '#ffffff', color, 3)
    text(draw, left + 53, 220, number, 28, color)
    for line_index, line in enumerate(title.split('\n')):
        text(draw, left + 150, 310 + line_index * 34, line, 20)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 410 + line_index * 36, line, 18, '#353535')
    if index < 3:
        arrow(draw, (left + 308, 345), (left + 337, 345), '#1f1f1f', 6, 14)
text(draw, 700, 640, 'Catatan lab: jangan matikan Wi-Fi laptop. Broker dan MQTTX tinggal di PC.', 18, '#b45309')
save(image, 'fs37-hotspot-demo.png')
