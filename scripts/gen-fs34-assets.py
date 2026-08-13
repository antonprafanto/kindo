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
    text(draw, width / 2, 57, title, 36)
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


# Cover — Mosquitto visibly in the middle, arrows with heads
image = Image.new('RGB', (1200, 675), '#12315c')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#1d64b8')
box(draw, (55, 85, 355, 340), '#fff8e1', '#ffffff', 4)
text(draw, 205, 145, 'DHT22 + ESP32', 28, '#b45309')
text(draw, 205, 205, 'baca suhu', 22)
text(draw, 205, 245, '& kelembapan', 22)
box(draw, (430, 75, 770, 350), '#1565c0', '#ffffff', 4)
text(draw, 600, 145, 'MOSQUITTO', 32, '#ffffff')
text(draw, 600, 205, 'broker di PC', 24, '#e3f2fd')
text(draw, 600, 255, 'kantor pos', 22, '#fff8e1')
text(draw, 600, 300, 'IPv4 LAN', 20, '#fff3b0')
box(draw, (845, 85, 1145, 340), '#e3f2fd', '#ffffff', 4)
text(draw, 995, 145, 'MQTTX', 32, '#1565c0')
text(draw, 995, 205, 'lihat JSON', 22)
text(draw, 995, 245, 'hidup', 22)
arrow(draw, (355, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (845, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'kodingindonesia/fsiot/esp32-meja-01/telemetry', 24, '#fff3b0')
text(draw, 600, 510, 'FS-34 · ESP32 mengirim telemetry DHT22 sebagai JSON', 28, '#ffffff')
text(draw, 600, 570, 'Host MQTTX = IPv4 PC, bukan 127.0.0.1', 22, '#dbeafe')
save(image, 'fs34-cover-telemetry.jpg')
image.save(OUT / 'fs34-cover-telemetry.webp', 'WEBP', quality=85)
print('fs34-cover-telemetry.webp', image.size)

# Tools order — five numbered cards with arrowheads
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-34 — lima langkah, jangan loncat', 'ESP32 baru Upload setelah library, kabel, dan broker siap')
steps = [
    ('1', 'Buka browser', 'baca langkah\n& sumber resmi', '#fff8e1', '#f9a825'),
    ('2', 'Arduino IDE', 'ikon buku\nLibrary Manager', '#e3f2fd', '#1565c0'),
    ('3', 'Kabel DHT22', '3V3 · GPIO 4\n· GND', '#e8f5e9', '#2e7d32'),
    ('4', 'PowerShell', 'ipconfig +\nbroker tetap hidup', '#fce4ec', '#c62828'),
    ('5', 'MQTTX', 'Host = IPv4 PC\nport 1883', '#f3e8ff', '#7e22ce'),
]
for index, (number, title, body, fill, color) in enumerate(steps):
    left = 16 + index * 277
    box(draw, (left, 165, left + 258, 510), fill, color)
    box(draw, (left + 14, 184, left + 76, 246), '#ffffff', color, 3)
    text(draw, left + 45, 215, number, 28, color)
    text(draw, left + 129, 300, title, 20)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 129, 365 + line_index * 36, line, 16, '#353535')
    if index < 4:
        arrow(draw, (left + 262, 338), (left + 273, 338), '#1f1f1f', 5, 12)
text(draw, 700, 585, 'Lab memakai Wi-Fi rumah yang sama. Jangan guest Wi-Fi, port router, atau broker publik.', 20, '#b91c1c')
save(image, 'fs34-tools-order.png')

# Official Arduino Library Manager screenshots + Indonesian banners
img01 = Image.open(OUT / 'fs34-arduino-lib-img01.png').convert('RGB')
img02 = Image.open(OUT / 'fs34-arduino-lib-img02.png').convert('RGB')
# Crop so the book icon / Library Manager pane is readable, not a tiny IDE in a tall window.
crop01 = img01.crop((0, 40, 1180, 980))
crop02 = img02.crop((0, 40, 1280, 1000))
panel_h = 430
crop01 = crop01.resize((int(crop01.width * panel_h / crop01.height), panel_h), Image.Resampling.LANCZOS)
crop02 = crop02.resize((int(crop02.width * panel_h / crop02.height), panel_h), Image.Resampling.LANCZOS)
gap = 16
inner_w = crop01.width + gap + crop02.width
target_w = 1400
pad_x = max(18, (target_w - inner_w) // 2)
banner_h, foot_h = 92, 100
composed_h = banner_h + panel_h + foot_h
library = Image.new('RGB', (target_w, composed_h), '#f5f5f0')
library.paste(crop01, (pad_x, banner_h))
library.paste(crop02, (pad_x + crop01.width + gap, banner_h))
dwn = ImageDraw.Draw(library)
box(dwn, (18, 10, target_w - 18, banner_h - 8), '#e3f2fd', '#1565c0')
text(dwn, target_w / 2, 36, 'Klik ikon buku di bilah kiri Arduino IDE 2', 24, '#0f3d7a')
text(dwn, target_w / 2, 68, 'Kiri: Library Manager. Kanan: ketik nama library, lalu INSTALL. Bukan menu Tools lama.', 16, '#1e3a5f')
box(dwn, (18, banner_h + panel_h + 8, target_w - 18, composed_h - 10), '#ffffff')
text(dwn, target_w / 2, banner_h + panel_h + 36, 'Sumber: docs.arduino.cc — Arduino IDE 2, Installing libraries. Arduino S.r.l. Tangkapan 13 Agustus 2026.', 15)
text(dwn, target_w / 2, banner_h + panel_h + 66, 'Dokumentasi Arduino berlisensi Creative Commons Attribution-Share Alike 4.0. Logo Arduino adalah merek Arduino S.r.l.', 14, '#353535')
save(library, 'fs34-library-manager.png')

# Wiring — arrows with heads, match labels not physical pin order
image = Image.new('RGB', (1200, 675), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Wiring DHT22 3-pin ke ESP32 — ikuti tulisan pin', 'Jangan menebak urutan kaki; modul bisa berbeda')
box(draw, (70, 175, 470, 530), '#e3f2fd', '#1565c0')
text(draw, 270, 220, 'ESP32 DevKitC-1', 28, '#1565c0')
for y, pin, color in [(300, '3V3', '#ef4444'), (380, 'GPIO 4', '#f59e0b'), (460, 'GND', '#374151')]:
    box(draw, (130, y - 28, 410, y + 28), '#ffffff', color, 3)
    text(draw, 270, y, pin, 24, color)
box(draw, (730, 175, 1130, 530), '#fff8e1', '#f59e0b')
text(draw, 930, 220, 'Modul DHT22 3-pin', 28, '#b45309')
for y, pin, color in [(300, 'VCC', '#ef4444'), (380, 'DATA / DAT', '#f59e0b'), (460, 'GND', '#374151')]:
    box(draw, (790, y - 28, 1070, y + 28), '#ffffff', color, 3)
    text(draw, 930, y, pin, 22, color)
for y, color in [(300, '#ef4444'), (380, '#f59e0b'), (460, '#374151')]:
    arrow(draw, (410, y), (790, y), color, 8, 18)
text(draw, 600, 590, 'Jika sensor berbentuk bare 4-pin, cocokkan panduan pin khususnya. Jangan menebak.', 18, '#b91c1c')
save(image, 'fs34-wiring-dht22.png')

# LAN boundary
image = Image.new('RGB', (1300, 690), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1300, 'ESP32 tidak boleh memakai localhost PC', 'ESP32 dan PC di Wi-Fi yang sama, tetapi masing-masing perangkat berbeda')
box(draw, (70, 175, 500, 545), '#e3f2fd', '#1565c0')
text(draw, 285, 230, 'ESP32', 34, '#1565c0')
text(draw, 285, 310, 'MQTT_HOST =', 24)
text(draw, 285, 365, '192.168.1.23', 30, '#b45309')
text(draw, 285, 430, 'bukan 127.0.0.1', 22, '#b91c1c')
text(draw, 285, 480, 'bukan localhost', 20, '#b91c1c')
box(draw, (800, 175, 1230, 545), '#e8f5e9', '#2e7d32')
text(draw, 1015, 230, 'PC / laptop', 32, '#2e7d32')
text(draw, 1015, 310, 'Mosquitto + MQTTX', 24)
text(draw, 1015, 365, '192.168.1.23', 30, '#b45309')
text(draw, 1015, 430, 'contoh saja', 22, '#475569')
text(draw, 1015, 480, 'dari ipconfig', 20, '#475569')
arrow(draw, (500, 360), (800, 360), '#7c3aed', 10, 22)
text(draw, 650, 300, 'Wi-Fi rumah', 22, '#7c3aed')
text(draw, 650, 610, 'Ganti 192.168.1.23 dengan IPv4 PC milikmu. 127.0.0.1 di ESP32 = ESP32 itu sendiri.', 18, '#334155')
save(image, 'fs34-lan-address.png')

# JSON flow — Mosquitto in the middle, left to right
image = Image.new('RGB', (1400, 700), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Baca dari kiri ke kanan: sensor, ESP32, broker, MQTTX', 'Mosquitto tetap di tengah, seperti kantor pos di FS-33')
boxes = [
    ((40, 200, 300, 520), '1. DHT22', '27.4 °C\n63.1 %', '#fff8e1', '#f9a825'),
    ((360, 175, 660, 545), '2. ESP32', 'bungkus JSON\ndevice_id +\nsuhu + lembap', '#e3f2fd', '#1565c0'),
    ((720, 175, 1020, 545), '3. Mosquitto', 'terima lalu\nteruskan\nIPv4 PC:1883', '#e8f5e9', '#2e7d32'),
    ((1080, 200, 1360, 520), '4. MQTTX', 'pesan JSON\nhidup', '#f3e8ff', '#7e22ce'),
]
for bounds, title, subtitle, fill, accent in boxes:
    box(draw, bounds, fill, accent)
    text(draw, (bounds[0] + bounds[2]) / 2, bounds[1] + 55, title, 26, accent)
    for line_index, line in enumerate(subtitle.split('\n')):
        text(draw, (bounds[0] + bounds[2]) / 2, bounds[1] + 160 + line_index * 36, line, 20)
arrow(draw, (300, 360), (360, 360), '#7c3aed', 8, 18)
arrow(draw, (660, 360), (720, 360), '#7c3aed', 8, 18)
arrow(draw, (1020, 360), (1080, 360), '#7c3aed', 8, 18)
text(draw, 700, 615, 'Topic: kodingindonesia/fsiot/esp32-meja-01/telemetry', 22, '#334155')
save(image, 'fs34-json-flow.png')

# Troubleshooting — four checks with arrows
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Jika pesan belum muncul, cek dari yang paling dekat', 'Jangan mengubah router; periksa empat titik ini satu per satu')
checks = [
    ('1', 'DHT22', 'kabel 3V3\nGPIO 4 · GND', '#fff8e1', '#f9a825'),
    ('2', 'Wi-Fi', 'ESP32 dan PC\njaringan sama', '#e3f2fd', '#1565c0'),
    ('3', 'Broker', 'PowerShell aktif\nIPv4 PC benar', '#e8f5e9', '#2e7d32'),
    ('4', 'MQTTX', 'Host = IPv4 PC\nport 1883', '#fce4ec', '#c62828'),
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
text(draw, 700, 575, 'Jika Windows bertanya soal jaringan, izinkan hanya Private di rumah tepercaya.', 20, '#b91c1c')
save(image, 'fs34-troubleshooting.png')
