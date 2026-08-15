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
text(draw, 215, 125, 'ESP32', 28, '#1565c0')
text(draw, 215, 180, 'kirim suhu', 22)
text(draw, 215, 230, 'patuh perintah', 20, '#475569')
text(draw, 215, 280, 'tanpa ambang di sketch', 18, '#475569')
box(draw, (430, 70, 770, 350), '#fff8e1', '#ffffff', 4)
text(draw, 600, 125, 'Node-RED di PC', 26, '#b45309')
text(draw, 600, 185, 'jika suhu > 30', 22)
text(draw, 600, 235, 'maka relay on', 20, '#92400e')
text(draw, 600, 285, 'ubah ambang di sini', 18, '#92400e')
box(draw, (810, 70, 1160, 350), '#ecfdf5', '#ffffff', 4)
text(draw, 985, 125, 'Relay GPIO 26', 26, '#166534')
text(draw, 985, 185, 'berklik', 22, '#14532d')
text(draw, 985, 235, 'bukan AC 220V', 20, '#14532d')
text(draw, 985, 285, 'tanpa Upload ulang', 18, '#166534')
arrow(draw, (390, 210), (430, 210), '#ffd54f', 10, 22)
arrow(draw, (770, 210), (810, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Hari ini otak aturan pindah ke PC. ESP32 tetap jadi perangkat.', 22, '#fff3b0')
text(draw, 600, 505, 'FS-38 · Jika-maka di Node-RED, tanpa Python', 26, '#ffffff')
text(draw, 600, 570, 'Ambang di PC  ·  bukan di sketch  ·  bukan AC 220V', 20, '#dbeafe')
save(image, 'fs38-cover-rules.jpg')
image.save(OUT / 'fs38-cover-rules.webp', 'WEBP', quality=85)
print('fs38-cover-rules.webp', image.size)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-38 — lima langkah, jangan loncat', 'Node.js dulu, baru Node-RED. MQTTX tetap terbuka sebagai saksi.')
steps = [
    ('1', 'Buka browser', 'baca langkah\ndan unduhan resmi', '#fff8e1', '#f9a825'),
    ('2', 'Node.js LTS', 'pemasang Windows\nlalu node -v', '#e3f2fd', '#1565c0'),
    ('3', 'Node-RED', 'PowerShell:\nnpm + node-red', '#e8f5e9', '#2e7d32'),
    ('4', 'Mosquitto\n+ MQTTX', 'Host = IPv4 PC\nport 1883', '#e0f2f1', '#00897b'),
    ('5', 'Arduino IDE', 'Upload sekali,\nambang di Node-RED', '#f3e8ff', '#7e22ce'),
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
text(draw, 700, 585, 'Catatan lab: Python ditunda ke FS-39. Jangan memasang script rules hari ini.', 17, '#b45309')
save(image, 'fs38-tools-order.png')

# Same home Wi-Fi
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Hari ini satu Wi-Fi rumah. Demo hotspot FS-37 tidak dipakai.', 'Node-RED di PC memakai 127.0.0.1. ESP32 memakai IPv4 PC.')
box(draw, (50, 150, 670, 560), '#ecfdf5', '#166534')
text(draw, 360, 200, 'PC / laptop', 28, '#166534')
text(draw, 360, 260, 'Wi-Fi rumah', 22)
text(draw, 360, 320, 'Mosquitto + MQTTX', 22)
text(draw, 360, 380, 'Node-RED :1880', 22)
text(draw, 360, 440, 'broker 127.0.0.1:1883', 20, '#14532d')
text(draw, 360, 500, 'jangan matikan router', 18, '#b45309')
box(draw, (730, 150, 1350, 560), '#e3f2fd', '#1565c0')
text(draw, 1040, 200, 'ESP32 + DHT22 + relay', 26, '#1565c0')
text(draw, 1040, 260, 'Wi-Fi rumah yang sama', 22)
text(draw, 1040, 320, 'MQTT_HOST = IPv4 PC', 20)
text(draw, 1040, 380, 'bukan 127.0.0.1', 20, '#1e3a8a')
text(draw, 1040, 440, 'bukan hotspot HP hari ini', 18, '#1e3a8a')
text(draw, 1040, 500, 'USB tetap terpasang', 18, '#1e3a8a')
arrow(draw, (670, 355), (730, 355), '#1f1f1f', 8, 18)
text(draw, 700, 640, 'Catatan lab: 127.0.0.1 di ESP32 = ESP32 itu sendiri. Ganti 192.168.1.23 dengan IPv4 PC milikmu.', 18, '#b45309')
save(image, 'fs38-same-wifi.png')

# Brain on PC vs firmware
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Firmware patuh. PC yang memutuskan jika-maka.', 'Ubah angka 30 di Node-RED, lalu Deploy. Jangan Upload ulang ESP32.')
box(draw, (60, 155, 660, 520), '#fff7ed', '#c2410c')
text(draw, 360, 220, 'Jangan di sketch', 26, '#c2410c')
text(draw, 360, 290, 'if (suhu > 30)', 22)
text(draw, 360, 350, 'ganti ambang = Upload lagi', 20)
text(draw, 360, 410, 'lab ini tidak memakai cara ini', 18, '#9a3412')
box(draw, (740, 155, 1340, 520), '#ecfdf5', '#166534')
text(draw, 1040, 220, 'Di Node-RED / MQTTX', 26, '#166534')
text(draw, 1040, 290, 'switch suhu > 30', 22)
text(draw, 1040, 350, 'ganti ambang = Deploy', 20, '#14532d')
text(draw, 1040, 410, 'ESP32 tidak di-flash ulang', 18, '#14532d')
text(draw, 700, 585, 'Catatan lab: satu aturan di satu tempat. Jangan dobel: if di sketch plus if di PC.', 18, '#b45309')
save(image, 'fs38-brain-on-pc.png')

# Left to right flow
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Baca dari kiri ke kanan: suhu, aturan PC, perintah, klik', 'ESP32 tidak memutuskan ambang.')
boxes = [
    (40, '1. Suhu', 'DHT22 GPIO 4\ntelemetry JSON', '#fff8e1', '#f9a825'),
    (380, '2. Broker', 'Mosquitto 1883\ndi PC', '#e3f2fd', '#1565c0'),
    (720, '3. Aturan', 'Node-RED\njika > 30 maka on', '#e8f5e9', '#2e7d32'),
    (1060, '4. Relay', 'GPIO 26 klik\nstatus JSON', '#ecfdf5', '#166534'),
]
for left, title, body, fill, color in boxes:
    box(draw, (left, 150, left + 300, 560), fill, color)
    text(draw, left + 150, 210, title, 26, color)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 320 + line_index * 48, line, 20, '#353535')
for left in (340, 680, 1020):
    arrow(draw, (left, 355), (left + 40, 355), '#1f1f1f', 6, 14)
text(draw, 700, 640, 'Catatan lab: MQTTX tetap terbuka supaya kamu melihat telemetri dan status, bukan mengganti Node-RED.', 18, '#b45309')
save(image, 'fs38-flow.png')

# Combined wiring
image = Image.new('RGB', (1400, 860), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Wiring hari ini: DHT22 GPIO 4 + relay GPIO 26', 'Ikuti tulisan pin. Terminal NC/COM/NO kosong. Bukan AC 220V.')
box(draw, (40, 145, 430, 720), '#e3f2fd', '#1565c0')
text(draw, 235, 185, 'ESP32 DevKitC-1', 24, '#1565c0')
for y, label in [(250, '3V3'), (320, 'GPIO 4 DATA'), (390, 'GPIO 26 IN'), (460, '5V'), (530, 'GND'), (600, 'GND')]:
    box(draw, (70, y - 28, 400, y + 28), '#ffffff', '#1565c0', 3)
    text(draw, 235, y, label, 20, '#1e3a8a')
box(draw, (520, 145, 880, 430), '#ecfdf5', '#166534')
text(draw, 700, 185, 'DHT22', 26, '#166534')
text(draw, 700, 255, 'VCC → 3V3', 22)
text(draw, 700, 315, 'DATA → GPIO 4', 22)
text(draw, 700, 375, 'GND → GND', 22)
box(draw, (520, 460, 880, 720), '#fff7ed', '#c2410c')
text(draw, 700, 500, 'Relay 5V', 26, '#c2410c')
text(draw, 700, 560, 'VCC/+ → 5V', 22)
text(draw, 700, 615, 'IN/S → GPIO 26', 22)
text(draw, 700, 670, 'GND/− → GND', 22)
box(draw, (960, 145, 1360, 720), '#fff8e1', '#f9a825')
text(draw, 1160, 200, 'Yang tidak', 26, '#b45309')
text(draw, 1160, 280, 'microSD / SPI', 22)
text(draw, 1160, 350, 'hotspot HP', 22)
text(draw, 1160, 420, 'Python', 22)
text(draw, 1160, 490, 'AC 220V', 22)
text(draw, 1160, 560, 'NC/COM/NO isi', 20, '#92400e')
text(draw, 1160, 640, 'ambang di sketch', 20, '#92400e')
text(draw, 700, 790, 'Catatan lab: jika modul relay aktif HIGH, ubah AKTIF_LOW menjadi false. Jangan menebak dari foto.', 18, '#b45309')
save(image, 'fs38-wiring.png')

# Node-RED editor illustration
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Node-RED sudah menampilkan alur jika-maka', 'http://127.0.0.1:1880  ·  klik Deploy setelah mengubah angka.')
box(draw, (50, 145, 300, 630), '#e2e8f0', '#cbd5e1', 0)
text(draw, 175, 185, 'Palette', 22, '#334155')
for y, label, fill in [(240, 'mqtt in', '#dbeafe'), (320, 'switch', '#dcfce7'), (400, 'change', '#fef9c3'), (480, 'mqtt out', '#dbeafe')]:
    box(draw, (70, y, 280, y + 58), fill, '#64748b', 3)
    text(draw, 175, y + 29, label, 18)
box(draw, (300, 145, 1350, 630), '#ffffff', '#e5e7eb', 0)
nodes = [
    (340, 300, 540, 400, '#dbeafe', '#2563eb', 'mqtt in\ntelemetry'),
    (590, 300, 810, 400, '#dcfce7', '#16a34a', 'switch\nsuhu > 30'),
    (860, 210, 1070, 310, '#fef9c3', '#ca8a04', 'change\nrelay on'),
    (860, 430, 1070, 530, '#fef9c3', '#ca8a04', 'change\nrelay off'),
    (1120, 300, 1330, 420, '#dbeafe', '#2563eb', 'mqtt out\ncommand'),
]
for x1, y1, x2, y2, fill, color, label in nodes:
    box(draw, (x1, y1, x2, y2), fill, color, 4)
    for line_index, line in enumerate(label.split('\n')):
        text(draw, (x1 + x2) / 2, y1 + 32 + line_index * 32, line, 18, color)
arrow(draw, (540, 350), (590, 350), '#1f1f1f', 6, 14)
arrow(draw, (810, 330), (860, 260), '#1f1f1f', 6, 14)
arrow(draw, (810, 370), (860, 480), '#1f1f1f', 6, 14)
arrow(draw, (1070, 260), (1120, 340), '#1f1f1f', 6, 14)
arrow(draw, (1070, 480), (1120, 390), '#1f1f1f', 6, 14)
box(draw, (1120, 155, 1330, 195), '#c2410c', '#9a3412', 0)
text(draw, 1225, 175, 'Deploy', 18, '#fff7ed')
text(draw, 700, 700, 'Ilustrasi buatan Koding Indonesia (FS-38), meniru editor Node-RED (OpenJS, Apache 2.0). Bukan screenshot jendela resmi.', 16, '#353535')
save(image, 'fs38-nodered-editor.png')

# Threshold change without upload
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Ubah ambang di Node-RED, lalu Deploy. Jangan Upload.', 'Arduino IDE boleh tertutup. USB ESP32 tetap colok.')
steps = [
    ('1', 'Double-klik\nswitch', 'angka 30\nsiap diubah', '#fff8e1', '#f9a825'),
    ('2', 'Ganti jadi 28\natau 32', 'sesuai ruanganmu', '#e3f2fd', '#1565c0'),
    ('3', 'Klik Deploy', 'tombol merah\nkanan atas', '#fff7ed', '#c2410c'),
    ('4', 'Relay berubah', 'tanpa Verify\ntanpa Upload', '#ecfdf5', '#166534'),
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
text(draw, 700, 640, 'Catatan lab: jika relay nyala-mati berganti cepat, naikkan ambang. Itu bukan kerusakan ESP32.', 18, '#b45309')
save(image, 'fs38-threshold-deploy.png')

# MQTTX manual path B
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Jalur cadangan: kamu yang jadi Node-RED di MQTTX', 'Lihat telemetri. Jika suhu di atas ambang, publish perintah on.')
box(draw, (50, 145, 1350, 630), '#ffffff', '#e5e7eb', 0)
text(draw, 700, 185, 'MQTTX  ·  Host IPv4 PC  ·  Port 1883', 24, '#166534')
box(draw, (80, 230, 660, 430), '#ecfdf5', '#34d399', 3)
text(draw, 370, 280, 'telemetry  live', 20, '#14532d')
text(draw, 370, 340, '{"temperature_c":31.2}', 20, '#14532d')
text(draw, 370, 390, 'lebih dari 30', 18, '#166534')
box(draw, (740, 230, 1320, 430), '#fff7ed', '#f59e0b', 3)
text(draw, 1030, 280, 'kamu publish command', 20, '#9a3412')
text(draw, 1030, 350, '{"device_id":"esp32-meja-01",', 18, '#9a3412')
text(draw, 1030, 390, '"relay":"on"}', 18, '#9a3412')
box(draw, (80, 460, 1320, 600), '#e0f2f1', '#0f766e', 3)
text(draw, 700, 510, 'Serial: Relay ON    MQTTX status: {"relay":"on"}', 22, '#134e4a')
text(draw, 700, 560, 'Ini jalur belajar. Automasi tetap di Node-RED pada jalur utama.', 18, '#134e4a')
text(draw, 700, 700, 'Ilustrasi MQTTX buatan Koding Indonesia (FS-38), meniru tata letak EMQ (Apache 2.0). 192.168.1.23 hanya contoh.', 16, '#353535')
save(image, 'fs38-mqttx-manual.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Jika relay tidak mengikuti ambang, cek dari yang paling dekat', 'Jangan mengganti pin GPIO 26. Empat titik ini dulu.')
checks = [
    ('1', 'Node-RED', 'jendela tetap\nbuka, :1880', '#fff8e1', '#f9a825'),
    ('2', 'Broker', 'Mosquitto 1883\n127.0.0.1 di PC', '#e3f2fd', '#1565c0'),
    ('3', 'Deploy', 'tombol merah\nsudah diklik', '#e8f5e9', '#2e7d32'),
    ('4', 'ESP32', 'IPv4 PC, bukan\n127.0.0.1', '#f3e8ff', '#7e22ce'),
]
for index, (number, title, body, fill, color) in enumerate(checks):
    left = 40 + index * 345
    box(draw, (left, 165, left + 300, 500), fill, color)
    box(draw, (left + 18, 185, left + 88, 255), '#ffffff', color, 3)
    text(draw, left + 53, 220, number, 28, color)
    text(draw, left + 150, 310, title, 26)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 375 + line_index * 38, line, 20, '#353535')
    if index < 3:
        arrow(draw, (left + 308, 332), (left + 337, 332), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: Python, Laragon, dan php artisan tidak dipakai hari ini. AC 220V tetap dilarang.', 18, '#b45309')
save(image, 'fs38-troubleshooting.png')
