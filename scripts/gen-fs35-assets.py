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


# Cover — command goes MQTTX → Mosquitto → ESP32+relay
image = Image.new('RGB', (1200, 675), '#12315c')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#1d64b8')
box(draw, (55, 85, 355, 340), '#e3f2fd', '#ffffff', 4)
text(draw, 205, 145, 'MQTTX', 32, '#1565c0')
text(draw, 205, 205, 'publish', 22)
text(draw, 205, 245, 'relay on/off', 22)
box(draw, (430, 75, 770, 350), '#1565c0', '#ffffff', 4)
text(draw, 600, 145, 'MOSQUITTO', 32, '#ffffff')
text(draw, 600, 205, 'broker di PC', 24, '#e3f2fd')
text(draw, 600, 255, 'kantor pos', 22, '#fff8e1')
text(draw, 600, 300, 'IPv4 LAN', 20, '#fff3b0')
box(draw, (845, 85, 1145, 340), '#fff8e1', '#ffffff', 4)
text(draw, 995, 145, 'ESP32 + relay', 26, '#b45309')
text(draw, 995, 205, 'GPIO 26', 24)
text(draw, 995, 245, 'klik + LED', 22)
arrow(draw, (355, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (845, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'kodingindonesia/fsiot/esp32-meja-01/command', 24, '#fff3b0')
text(draw, 600, 510, 'FS-35 · Perintah MQTT menggerakkan relay', 28, '#ffffff')
text(draw, 600, 570, 'Host MQTTX = IPv4 PC, bukan 127.0.0.1', 22, '#dbeafe')
save(image, 'fs35-cover-command.jpg')
image.save(OUT / 'fs35-cover-command.webp', 'WEBP', quality=85)
print('fs35-cover-command.webp', image.size)

# Tools order — five numbered cards
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-35 — lima langkah, jangan loncat', 'Relay baru diuji setelah library, kabel, dan broker siap')
steps = [
    ('1', 'Buka browser', 'baca langkah\n& sumber resmi', '#fff8e1', '#f9a825'),
    ('2', 'Arduino IDE', 'ikon buku\nArduinoMqttClient', '#e3f2fd', '#1565c0'),
    ('3', 'Kabel relay', '5V · GPIO 26\n· GND', '#e8f5e9', '#2e7d32'),
    ('4', 'PowerShell', 'ipconfig +\nbroker tetap hidup', '#fce4ec', '#c62828'),
    ('5', 'MQTTX', 'Host = IPv4 PC\npublish perintah', '#f3e8ff', '#7e22ce'),
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
text(draw, 700, 585, 'Belum AC 220V. Lab memakai Wi-Fi rumah yang sama. Jangan guest Wi-Fi atau broker publik.', 18, '#b91c1c')
save(image, 'fs35-tools-order.png')

# Wiring — labels not physical pin order
image = Image.new('RGB', (1200, 675), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Wiring relay 5V ke ESP32 — ikuti tulisan pin', 'Jangan menebak urutan kaki; NC/COM/NO hari ini kosong')
box(draw, (70, 175, 470, 530), '#e3f2fd', '#1565c0')
text(draw, 270, 220, 'ESP32 DevKitC-1', 28, '#1565c0')
for y, pin, color in [(300, '5V', '#ef4444'), (380, 'GPIO 26', '#1565c0'), (460, 'GND', '#374151')]:
    box(draw, (130, y - 28, 410, y + 28), '#ffffff', color, 3)
    text(draw, 270, y, pin, 24, color)
box(draw, (730, 175, 1130, 530), '#fff8e1', '#f59e0b')
text(draw, 930, 220, 'Modul relay 5V', 28, '#b45309')
for y, pin, color in [(300, 'VCC / +', '#ef4444'), (380, 'IN / S', '#1565c0'), (460, 'GND / −', '#374151')]:
    box(draw, (790, y - 28, 1070, y + 28), '#ffffff', color, 3)
    text(draw, 930, y, pin, 22, color)
for y, color in [(300, '#ef4444'), (380, '#1565c0'), (460, '#374151')]:
    arrow(draw, (410, y), (790, y), color, 8, 18)
text(draw, 600, 590, 'Bukan AC 220V. Terminal sekrup NC/COM/NO boleh kosong. Bukti sukses = klik + LED.', 18, '#b91c1c')
save(image, 'fs35-wiring-relay.png')

# LAN boundary
image = Image.new('RGB', (1300, 690), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1300, 'ESP32 tidak boleh memakai localhost PC', 'Sama seperti FS-34: ESP32 adalah perangkat lain di Wi-Fi rumah')
box(draw, (70, 175, 500, 545), '#e3f2fd', '#1565c0')
text(draw, 285, 230, 'ESP32 + relay', 30, '#1565c0')
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
save(image, 'fs35-lan-address.png')

# Command flow — left to right, status returns
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Baca dari kiri ke kanan: perintah turun, status kembali', 'Mosquitto tetap di tengah, seperti kantor pos')
boxes = [
    ((40, 160, 300, 430), '1. MQTTX', 'publish JSON\nrelay on/off', '#f3e8ff', '#7e22ce'),
    ((360, 160, 640, 430), '2. Mosquitto', 'terima lalu\nteruskan', '#e8f5e9', '#2e7d32'),
    ((700, 160, 980, 430), '3. ESP32', 'subscribe\nGPIO 26', '#e3f2fd', '#1565c0'),
    ((1040, 160, 1360, 430), '4. Relay', 'klik + LED\nbukan 220V', '#fff8e1', '#f9a825'),
]
for bounds, title, subtitle, fill, accent in boxes:
    box(draw, bounds, fill, accent)
    text(draw, (bounds[0] + bounds[2]) / 2, bounds[1] + 50, title, 24, accent)
    for line_index, line in enumerate(subtitle.split('\n')):
        text(draw, (bounds[0] + bounds[2]) / 2, bounds[1] + 145 + line_index * 36, line, 18)
arrow(draw, (300, 295), (360, 295), '#7c3aed', 8, 18)
arrow(draw, (640, 295), (700, 295), '#7c3aed', 8, 18)
arrow(draw, (980, 295), (1040, 295), '#7c3aed', 8, 18)
arrow(draw, (1040, 470), (300, 470), '#0d9488', 7, 16)
text(draw, 700, 505, 'status JSON kembali ke MQTTX lewat topic /status', 20, '#0f766e')
text(draw, 700, 575, 'command: .../esp32-meja-01/command   ·   status: .../esp32-meja-01/status', 18, '#334155')
text(draw, 700, 655, 'Siapa saja di Wi-Fi rumah bisa mengirim perintah lab ini. Hentikan broker dengan Ctrl+C.', 18, '#b91c1c')
save(image, 'fs35-command-flow.png')

# MQTTX publish illustration — not an official screenshot
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Ini tampilan yang benar — publish perintah di MQTTX', 'Host = IPv4 PC. Bukan layar error. Bukan broker publik.')
box(draw, (50, 145, 1350, 630), '#1f2937', '#111827', 4)
box(draw, (50, 145, 128, 630), '#111827', '#111827', 0)
text(draw, 89, 205, 'X', 36, '#34d399')
text(draw, 89, 275, '+', 28, '#9ca3af')
box(draw, (128, 145, 430, 630), '#1f2937', '#1f2937', 0)
text(draw, 279, 190, 'Koneksi', 22, '#e5e7eb')
box(draw, (150, 225, 410, 345), '#065f46', '#34d399', 3)
text(draw, 280, 260, 'FS35 perintah LAN', 18, '#ecfdf5')
text(draw, 280, 300, 'tersambung', 18, '#a7f3d0')
box(draw, (430, 145, 1350, 630), '#f8fafc', '#e5e7eb', 0)
text(draw, 890, 180, 'Host  192.168.1.23', 26, '#166534')
text(draw, 890, 220, 'Port  1883', 26, '#166534')
text(draw, 890, 265, 'Publish  .../command', 20, '#334155')
text(draw, 890, 305, 'Subscribe  .../status', 20, '#334155')
box(draw, (500, 340, 1280, 500), '#ecfdf5', '#34d399', 4)
text(draw, 890, 380, '{"device_id":"esp32-meja-01",', 20, '#14532d')
text(draw, 890, 420, '"relay":"on"}', 20, '#14532d')
text(draw, 890, 465, 'lalu ganti "off" untuk mematikan', 16, '#166534')
box(draw, (560, 525, 1220, 595), '#e8f5e9', '#2e7d32', 4)
text(draw, 890, 560, 'Bukan 127.0.0.1. Bukan broker publik.', 20, '#14532d')
text(draw, 700, 700, 'Ilustrasi MQTTX buatan Koding Indonesia (FS-35), meniru tata letak aplikasi resmi EMQ. 192.168.1.23 hanya contoh.', 16, '#353535')
save(image, 'fs35-mqttx-publish.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Jika relay belum klik, cek dari yang paling dekat', 'Jangan mengubah router; periksa empat titik ini satu per satu')
checks = [
    ('1', 'Relay', '5V · GPIO 26\nGND · aktif LOW', '#fff8e1', '#f9a825'),
    ('2', 'Wi-Fi', 'ESP32 dan PC\njaringan sama', '#e3f2fd', '#1565c0'),
    ('3', 'Broker', 'PowerShell aktif\nIPv4 PC benar', '#e8f5e9', '#2e7d32'),
    ('4', 'MQTTX', 'topic command\nJSON persis', '#fce4ec', '#c62828'),
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
text(draw, 700, 575, 'JSON harus huruf kecil on/off dan device_id persis. Bukan AC 220V.', 20, '#b91c1c')
save(image, 'fs35-troubleshooting.png')
