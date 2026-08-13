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

# Library Manager illustration — official Arduino docs screenshots are dark/dimmed
# and show an UNO, which looks like a broken image to beginners (same lesson as FS-32 MQTTX).
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Ini tampilan yang benar, bukan layar error', 'Klik ikon tiga buku. Papan contoh: ESP32, bukan UNO')
box(draw, (40, 145, 1360, 620), '#ffffff', '#1f2937', 4)
box(draw, (40, 145, 1360, 198), '#0f172a', '#0f172a', 0)
text(draw, 360, 172, 'FS34_dht22_mqtt_json  ·  Arduino IDE 2', 20, '#e2e8f0')
box(draw, (980, 156, 1320, 188), '#065f46', '#34d399', 2)
text(draw, 1150, 172, 'ESP32 Dev Module', 16, '#ecfdf5')
box(draw, (40, 198, 118, 620), '#111827', '#111827', 0)
for y in (250, 330, 490, 570):
    box(draw, (52, y - 22, 106, y + 22), '#1e293b', '#334155', 3)
box(draw, (52, 388, 106, 432), '#14532d', '#fbbf24', 3)
box(draw, (62, 396, 72, 424), '#fde68a', '#f59e0b', 2)
box(draw, (74, 392, 84, 424), '#fbbf24', '#d97706', 2)
box(draw, (86, 398, 96, 424), '#f59e0b', '#b45309', 2)
box(draw, (118, 198, 520, 620), '#f8fafc', '#cbd5e1', 0)
text(draw, 319, 230, 'Library Manager', 22, '#0f172a')
box(draw, (140, 258, 498, 300), '#ffffff', '#1565c0', 3)
text(draw, 319, 279, 'DHT sensor library', 18, '#1e3a5f')
box(draw, (140, 320, 498, 500), '#ffffff', '#86efac', 4)
text(draw, 319, 350, 'DHT sensor library', 20, '#14532d')
text(draw, 319, 385, 'oleh Adafruit', 16, '#166534')
text(draw, 319, 420, 'Install All jika diminta', 16, '#334155')
box(draw, (300, 445, 478, 485), '#0d9488', '#0f766e', 3)
text(draw, 389, 465, 'INSTALL', 18, '#ffffff')
box(draw, (520, 198, 1360, 620), '#ffffff', '#e5e7eb', 0)
text(draw, 940, 250, 'void setup() {', 22, '#94a3b8')
text(draw, 940, 295, '}', 22, '#94a3b8')
text(draw, 940, 350, 'void loop() {', 22, '#94a3b8')
text(draw, 940, 395, '}', 22, '#94a3b8')
box(draw, (620, 450, 1260, 575), '#e8f5e9', '#2e7d32', 4)
text(draw, 940, 490, 'Ini tampilan yang benar, bukan layar error.', 22, '#14532d')
text(draw, 940, 535, 'Bukan menu Tools lama. Bukan screenshot gelap.', 18, '#166534')
arrow(draw, (118, 410), (210, 410), '#f59e0b', 6, 14)
text(draw, 700, 700, 'Ilustrasi buatan Koding Indonesia (FS-34), meniru Arduino IDE 2. Acuan: docs.arduino.cc (CC BY-SA 4.0).', 16, '#353535')
save(image, 'fs34-library-manager.png')

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
