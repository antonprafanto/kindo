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


# Cover — ESP32 writes CSV onto microSD, then the PC reads it
image = Image.new('RGB', (1200, 675), '#12315c')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#1d64b8')
box(draw, (50, 80, 360, 345), '#e3f2fd', '#ffffff', 4)
text(draw, 205, 135, 'ESP32', 32, '#1565c0')
text(draw, 205, 190, 'DHT22 GPIO 4', 22)
text(draw, 205, 235, 'tulis baris CSV', 22)
text(draw, 205, 280, 'setiap 5 detik', 20, '#475569')
box(draw, (420, 70, 780, 355), '#1565c0', '#ffffff', 4)
text(draw, 600, 135, 'microSD SPI', 30, '#ffffff')
text(draw, 600, 195, 'CS 5  SCK 18', 22, '#e3f2fd')
text(draw, 600, 240, 'MISO 19  MOSI 23', 22, '#e3f2fd')
text(draw, 600, 290, 'FAT32  ·  log.csv', 22, '#fff8e1')
box(draw, (840, 80, 1150, 345), '#fff8e1', '#ffffff', 4)
text(draw, 995, 135, 'Komputer', 28, '#b45309')
text(draw, 995, 195, 'File Explorer', 22)
text(draw, 995, 240, 'buka log.csv', 22)
text(draw, 995, 285, 'Notepad / Excel', 20, '#475569')
arrow(draw, (360, 210), (420, 210), '#ffd54f', 10, 22)
arrow(draw, (780, 210), (840, 210), '#ffd54f', 10, 22)
text(draw, 600, 430, 'Hari ini tidak MQTT. Data disimpan di kartu dulu.', 24, '#fff3b0')
text(draw, 600, 510, 'FS-36 · Simpan data ke microSD saat akan offline', 26, '#ffffff')
text(draw, 600, 570, 'Uji pertama: PAKAI_NTP = false  ·  kolom timestamp_ms', 20, '#dbeafe')
save(image, 'fs36-cover-sd.jpg')
image.save(OUT / 'fs36-cover-sd.webp', 'WEBP', quality=85)
print('fs36-cover-sd.webp', image.size)

# Tools order — five numbered cards
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-36 — lima langkah, jangan loncat', 'Kartu baru ditulis setelah format FAT32 dan kabel SPI siap')
steps = [
    ('1', 'Buka browser', 'baca langkah\ndan sumber resmi', '#fff8e1', '#f9a825'),
    ('2', 'File Explorer', 'format kartu\nFAT32', '#e3f2fd', '#1565c0'),
    ('3', 'Arduino IDE', 'SD.h sudah ada\ndi core ESP32', '#e8f5e9', '#2e7d32'),
    ('4', 'Kabel SPI', 'CS 5 · SCK 18\nMISO 19 · MOSI 23', '#e0f2f1', '#00897b'),
    ('5', 'Serial Monitor', 'lalu cabut kartu\nbaca log.csv', '#f3e8ff', '#7e22ce'),
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
text(draw, 700, 585, 'Catatan lab: hari ini tidak MQTTX, Mosquitto, atau relay. Serial Monitor = menu Tools, bukan ikon buku.', 18, '#b45309')
save(image, 'fs36-tools-order.png')

# FAT32 format illustration — not an official Windows screenshot
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Klik kanan kartu di File Explorer, lalu Format', 'Sistem berkas: FAT32. Kartu 8–32 GB paling mudah.')
box(draw, (50, 145, 480, 620), '#ffffff', '#1f2937', 4)
box(draw, (50, 145, 480, 198), '#0f172a', '#0f172a', 0)
text(draw, 265, 172, 'File Explorer', 22, '#e2e8f0')
box(draw, (80, 230, 450, 320), '#dbeafe', '#1565c0', 4)
text(draw, 265, 260, 'Kartu SD (E:)', 22, '#1e3a8a')
text(draw, 265, 295, 'dipilih', 18, '#1d4ed8')
box(draw, (80, 360, 450, 560), '#fff7ed', '#f59e0b', 4)
text(draw, 265, 400, 'Klik kanan', 22, '#b45309')
text(draw, 265, 450, 'Format...', 22)
text(draw, 265, 500, 'lalu pilih FAT32', 18, '#475569')
box(draw, (530, 145, 1350, 620), '#ffffff', '#1f2937', 4)
box(draw, (530, 145, 1350, 198), '#e2e8f0', '#cbd5e1', 0)
text(draw, 940, 172, 'Format (E:)', 24, '#0f172a')
text(draw, 760, 250, 'Sistem berkas', 18, '#475569')
box(draw, (900, 225, 1280, 275), '#ecfdf5', '#34d399', 4)
text(draw, 1090, 250, 'FAT32', 24, '#14532d')
text(draw, 760, 330, 'Label volume', 18, '#475569')
box(draw, (900, 305, 1280, 355), '#ffffff', '#cbd5e1', 3)
text(draw, 1090, 330, 'FSIOT', 22, '#334155')
box(draw, (680, 390, 1280, 455), '#fffbeb', '#f59e0b', 3)
text(draw, 980, 422, 'Format cepat  (boleh dicentang)', 20, '#92400e')
box(draw, (900, 500, 1120, 565), '#1565c0', '#1d4ed8', 4)
text(draw, 1010, 532, 'Mulai', 24, '#ffffff')
box(draw, (1150, 500, 1280, 565), '#e2e8f0', '#94a3b8', 3)
text(draw, 1215, 532, 'Batal', 20, '#334155')
text(draw, 700, 700, 'Ilustrasi langkah Format, buatan Koding Indonesia (FS-36). Windows adalah merek Microsoft.', 16, '#353535')
save(image, 'fs36-format-fat32.png')

# Typical 6-pin kit module — shops often sell this shape, not the 8-pin Adafruit photo
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Modul kit toko sering berbentuk papan enam pin', 'Baca tulisan pin. Urutan kaki fisik boleh berbeda.')
box(draw, (80, 150, 620, 620), '#1d4ed8', '#1e3a8a')
text(draw, 350, 190, 'microSD  SPI', 28, '#ffffff')
box(draw, (140, 230, 560, 330), '#cbd5e1', '#94a3b8', 3)
text(draw, 350, 265, 'slot kartu', 22, '#0f172a')
text(draw, 350, 305, 'masukkan microSD di sini', 18, '#334155')
kit_pins = ['CS', 'SCK', 'MOSI', 'MISO', 'VCC', 'GND']
for index, label in enumerate(kit_pins):
    x = 130 + index * 80
    box(draw, (x, 380, x + 64, 520), '#fde68a', '#b45309', 3)
    text(draw, x + 32, 450, label, 16, '#78350f')
text(draw, 350, 575, 'enam pin  ·  contoh rupa, bukan urutan wajib', 18, '#e0e7ff')
box(draw, (700, 170, 1320, 600), '#ffffff', '#1565c0')
text(draw, 1010, 220, 'Yang kamu cocokkan', 26, '#1565c0')
pairs = [
    'CS / SS          →  GPIO 5',
    'SCK / CLK        →  GPIO 18',
    'MISO / DO        →  GPIO 19',
    'MOSI / DI        →  GPIO 23',
    'VCC              →  5V atau 3V3',
    'GND              →  GND',
]
for index, line in enumerate(pairs):
    text(draw, 1010, 290 + index * 42, line, 20, '#0f172a')
text(draw, 700, 670, 'Catatan lab: foto Adafruit delapan pin di artikel ini bentuk lain. Kit biru enam pin tetap SPI.', 18, '#b45309')
save(image, 'fs36-modul-kit.png')

# Wiring — one arrow per net, GND on the SD module too
image = Image.new('RGB', (1400, 860), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Wiring microSD SPI + DHT22 — ikuti tulisan pin', 'Setiap kabel punya pasangan. GND modul SD juga harus tersambung.')
box(draw, (40, 145, 400, 790), '#e3f2fd', '#1565c0')
text(draw, 220, 180, 'ESP32 DevKitC-1', 22, '#1565c0')
esp_pins = [
    (240, '5V', '#ef4444'),
    (310, '3V3', '#f97316'),
    (380, 'GPIO 5  CS', '#1565c0'),
    (450, 'GPIO 18  SCK', '#0f766e'),
    (520, 'GPIO 19  MISO', '#7c3aed'),
    (590, 'GPIO 23  MOSI', '#c026d3'),
    (660, 'GPIO 4  DATA', '#2563eb'),
    (730, 'GND', '#374151'),
]
for y, label, color in esp_pins:
    box(draw, (70, y - 26, 370, y + 26), '#ffffff', color, 3)
    text(draw, 220, y, label, 18, color)
box(draw, (520, 145, 900, 790), '#fff8e1', '#f59e0b')
text(draw, 710, 180, 'Modul microSD SPI', 22, '#b45309')
sd_pins = [
    (240, 'VCC  (5V / 3V3)', '#ef4444'),
    (380, 'CS / SS', '#1565c0'),
    (450, 'SCK / CLK', '#0f766e'),
    (520, 'MISO / DO', '#7c3aed'),
    (590, 'MOSI / DI', '#c026d3'),
    (730, 'GND', '#374151'),
]
for y, label, color in sd_pins:
    box(draw, (550, y - 26, 870, y + 26), '#ffffff', color, 3)
    text(draw, 710, y, label, 18, color)
box(draw, (1020, 145, 1360, 790), '#e8f5e9', '#2e7d32')
text(draw, 1190, 180, 'DHT22', 22, '#2e7d32')
dht_pins = [
    (310, 'VCC → 3V3', '#f97316'),
    (660, 'DATA → GPIO 4', '#2563eb'),
    (730, 'GND', '#374151'),
]
for y, label, color in dht_pins:
    box(draw, (1050, y - 26, 1330, y + 26), '#ffffff', color, 3)
    text(draw, 1190, y, label, 18, color)
for y, color in [(240, '#ef4444'), (380, '#1565c0'), (450, '#0f766e'), (520, '#7c3aed'), (590, '#c026d3'), (730, '#374151')]:
    arrow(draw, (370, y), (550, y), color, 6, 14)
arrow(draw, (370, 310), (1050, 310), '#f97316', 6, 14)
arrow(draw, (370, 660), (1050, 660), '#2563eb', 6, 14)
arrow(draw, (870, 730), (1050, 730), '#374151', 6, 14)
text(draw, 700, 825, 'Catatan lab: jika modul SD hanya tercetak 3V3, VCC ke 3V3. Sinyal SPI jangan ke 5V. Bukan AC 220V.', 18, '#b45309')
save(image, 'fs36-wiring-spi.png')

# CSV flow left to right
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Baca dari kiri ke kanan: suhu menjadi baris di kartu', 'Hari ini tidak lewat Mosquitto. File log.csv yang dibawa ke PC.')
boxes = [
    ((40, 160, 300, 430), '1. DHT22', 'suhu Celsius\nGPIO 4', '#fff8e1', '#f9a825'),
    ((360, 160, 640, 430), '2. ESP32', 'gabung waktu\n+ suhu', '#e3f2fd', '#1565c0'),
    ((700, 160, 980, 430), '3. log.csv', 'tulis baris\nke FAT32', '#e8f5e9', '#2e7d32'),
    ((1040, 160, 1360, 430), '4. Komputer', 'cabut kartu\nbuka di PC', '#f3e8ff', '#7e22ce'),
]
for bounds, title, subtitle, fill, accent in boxes:
    box(draw, bounds, fill, accent)
    text(draw, (bounds[0] + bounds[2]) / 2, bounds[1] + 50, title, 24, accent)
    for line_index, line in enumerate(subtitle.split('\n')):
        text(draw, (bounds[0] + bounds[2]) / 2, bounds[1] + 145 + line_index * 36, line, 18)
arrow(draw, (300, 295), (360, 295), '#7c3aed', 8, 18)
arrow(draw, (640, 295), (700, 295), '#7c3aed', 8, 18)
arrow(draw, (980, 295), (1040, 295), '#7c3aed', 8, 18)
text(draw, 700, 505, 'Contoh baris uji pertama:  5123,27.4     (timestamp_ms , temperature_c)', 20, '#0f766e')
text(draw, 700, 560, 'Setelah PAKAI_NTP = true:  2026-08-14 10:15:03,27.4', 20, '#334155')
text(draw, 700, 640, 'Catatan lab: cabut kartu hanya setelah Serial berhenti menulis, atau setelah USB dicabut.', 18, '#b45309')
save(image, 'fs36-csv-flow.png')

# millis vs NTP
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'millis bukan jam dinding. NTP adalah jam internet.', 'Uji pertama tanpa Wi-Fi. Baru kemudian nyalakan PAKAI_NTP.')
box(draw, (50, 155, 670, 520), '#e3f2fd', '#1565c0')
text(draw, 360, 205, 'PAKAI_NTP = false', 28, '#1565c0')
text(draw, 360, 270, 'Kolom: timestamp_ms', 22)
text(draw, 360, 330, '5123,27.4', 26, '#0f172a')
text(draw, 360, 390, 'Angka milidetik sejak ESP32 nyala.', 18, '#334155')
text(draw, 360, 440, 'Reset setiap Upload / colok USB.', 18, '#334155')
box(draw, (730, 155, 1350, 520), '#e8f5e9', '#2e7d32')
text(draw, 1040, 205, 'PAKAI_NTP = true', 28, '#2e7d32')
text(draw, 1040, 270, 'Kolom: waktu_wib', 22)
text(draw, 1040, 330, '2026-08-14 10:15:03,27.4', 22, '#0f172a')
text(draw, 1040, 390, 'Butuh Wi-Fi rumah + internet.', 18, '#334155')
text(draw, 1040, 440, 'Jam WIB (GMT+7) untuk grafik FS-45.', 18, '#334155')
text(draw, 700, 585, 'Catatan lab: NTP tanpa Wi-Fi tidak jalan. Jangan mengira millis = jam dinding.', 18, '#b45309')
save(image, 'fs36-millis-vs-ntp.png')

# Serial Monitor illustration — light chrome, concrete success lines
image = Image.new('RGB', (1400, 720), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Buka Tools → Serial Monitor, baud 115200', 'Cari tulisan Kartu siap. Menulis /log.csv')
box(draw, (50, 145, 1350, 600), '#ffffff', '#1f2937', 4)
box(draw, (50, 145, 1350, 205), '#e2e8f0', '#cbd5e1', 0)
text(draw, 280, 175, 'Serial Monitor', 24, '#0f172a')
box(draw, (1080, 158, 1310, 192), '#065f46', '#34d399', 2)
text(draw, 1195, 175, '115200 baud', 16, '#ecfdf5')
lines = [
    ('Kartu siap. Menulis /log.csv', '#14532d'),
    ('Mode millis: kolom timestamp_ms. Bukan jam dinding.', '#1e3a8a'),
    ('Tersimpan: 5123,27.4', '#0f172a'),
    ('Tersimpan: 10124,27.5', '#0f172a'),
    ('Tersimpan: 15130,27.4', '#0f172a'),
]
for index, (line, color) in enumerate(lines):
    y = 255 + index * 58
    box(draw, (90, y - 24, 1310, y + 24), '#f8fafc' if index else '#ecfdf5', '#86efac' if index == 0 else '#e2e8f0', 3)
    text(draw, 700, y, line, 20, color)
text(draw, 700, 660, 'Ilustrasi buatan Koding Indonesia (FS-36), meniru Serial Monitor Arduino IDE 2. Menu: Tools → Serial Monitor.', 16, '#353535')
save(image, 'fs36-serial-monitor.png')

# Troubleshooting
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Jika kartu tidak terbaca, cek dari yang paling dekat', 'Jangan mengganti pin CS. Empat titik ini dulu.')
checks = [
    ('1', 'CS GPIO 5', 'SCK 18 · MISO 19\nMOSI 23', '#fff8e1', '#f9a825'),
    ('2', 'FAT32', 'bukan NTFS\nbukan exFAT', '#e3f2fd', '#1565c0'),
    ('3', 'GND bersama', 'ESP32 dan modul\nsatu GND', '#e8f5e9', '#2e7d32'),
    ('4', 'VCC sesuai label', '5V atau 3V3\nsinyal tetap 3,3 V', '#f3e8ff', '#7e22ce'),
]
for index, (number, title, body, fill, color) in enumerate(checks):
    left = 40 + index * 345
    box(draw, (left, 165, left + 300, 500), fill, color)
    box(draw, (left + 18, 185, left + 88, 255), '#ffffff', color, 3)
    text(draw, left + 53, 220, number, 28, color)
    text(draw, left + 150, 310, title, 24)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 375 + line_index * 38, line, 20, '#353535')
    if index < 3:
        arrow(draw, (left + 308, 332), (left + 337, 332), '#1f1f1f', 6, 14)
text(draw, 700, 575, 'Catatan lab: kartu palsu atau slot longgar juga gagal. Cabut USB dulu sebelum merapikan kabel.', 18, '#b45309')
save(image, 'fs36-troubleshooting.png')
