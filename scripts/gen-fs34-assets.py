from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


OUT = Path(__file__).resolve().parents[1] / 'public' / 'images' / 'fsiot'
OUT.mkdir(parents=True, exist_ok=True)
FONT_BOLD = 'C:/Windows/Fonts/arialbd.ttf'
FONT = 'C:/Windows/Fonts/arial.ttf'


def typeface(size, bold=True):
    return ImageFont.truetype(FONT_BOLD if bold else FONT, size)


def label(draw, x, y, value, size=28, color='#1f2937'):
    draw.multiline_text((x, y), value, font=typeface(size), fill=color, anchor='mm', align='center', spacing=8)


def panel(draw, bounds, fill='#ffffff', outline='#1f2937', width=4):
    draw.rounded_rectangle(bounds, radius=20, fill=fill, outline=outline, width=width)


def heading(draw, width, title, subtitle):
    panel(draw, (24, 20, width - 24, 125), '#ffffff')
    label(draw, width / 2, 58, title, 40)
    label(draw, width / 2, 98, subtitle, 21, '#475569')


def save(image, filename):
    image.save(OUT / filename, optimize=True)
    print(filename)


# Cover
image = Image.new('RGB', (1200, 675), '#13335f')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 300, 1200, 675), fill='#2368b9')
for bounds, title, subtitle, fill, accent in [
    ((70, 110, 335, 365), 'DHT22 + ESP32', 'baca suhu\n& kelembapan', '#fff8e1', '#f59e0b'),
    ((468, 110, 733, 365), 'Mosquitto', 'broker di PC\nIP LAN', '#e8f5e9', '#2e7d32'),
    ((865, 110, 1130, 365), 'MQTTX', 'lihat JSON\nhidup', '#e3f2fd', '#1565c0'),
]:
    panel(draw, bounds, fill, '#ffffff')
    label(draw, (bounds[0] + bounds[2]) / 2, 185, title, 30, accent)
    label(draw, (bounds[0] + bounds[2]) / 2, 270, subtitle, 25)
draw.line((335, 238, 468, 238), fill='#ffd54f', width=11)
draw.line((733, 238, 865, 238), fill='#ffd54f', width=11)
label(draw, 600, 450, 'kodingindonesia/fsiot/esp32-meja-01/telemetry', 24, '#fff3b0')
label(draw, 600, 550, 'FS-34 · ESP32 mengirim telemetry DHT22 sebagai JSON', 32, '#ffffff')
save(image, 'fs34-cover-telemetry.jpg')
image.save(OUT / 'fs34-cover-telemetry.webp', 'WEBP', quality=85)


# Tools order
image = Image.new('RGB', (1500, 700), '#f5f5f0')
draw = ImageDraw.Draw(image)
heading(draw, 1500, 'Urutan tools FS-34 — ESP32 mulai mengirim', 'Jangan membuka semuanya sekaligus; ikuti urutan dari kiri ke kanan')
steps = [
    ('1', 'Browser', 'baca langkah\n& sumber resmi', '#fff8e1', '#f59e0b'),
    ('2', 'Arduino IDE', 'Library Manager\n+ sketch', '#e3f2fd', '#1565c0'),
    ('3', 'Kabel DHT22', '3V3 · GPIO 4\n· GND', '#e8f5e9', '#2e7d32'),
    ('4', 'PowerShell', 'IP PC + broker\ntetap hidup', '#fce7f3', '#db2777'),
    ('5', 'MQTTX', 'subscribe JSON\ndi IP PC', '#f3e8ff', '#7e22ce'),
]
for index, (number, title, subtitle, fill, accent) in enumerate(steps):
    left = 34 + index * 292
    panel(draw, (left, 180, left + 258, 530), fill, accent)
    panel(draw, (left + 20, 202, left + 78, 260), '#ffffff', accent, 3)
    label(draw, left + 49, 232, number, 25, accent)
    label(draw, left + 129, 330, title, 25)
    label(draw, left + 129, 410, subtitle, 20, '#334155')
label(draw, 750, 620, 'Lab ini memakai Wi-Fi rumah yang sama; jangan gunakan guest Wi-Fi atau membuka port router.', 24, '#991b1b')
save(image, 'fs34-tools-order.png')


# Wiring
image = Image.new('RGB', (1200, 675), '#f5f5f0')
draw = ImageDraw.Draw(image)
heading(draw, 1200, 'Wiring DHT22 3-pin ke ESP32', 'Pastikan tulisan pin pada modul terbaca sebelum kabel dipasang')
panel(draw, (85, 190, 470, 535), '#e3f2fd', '#1565c0')
label(draw, 277, 235, 'ESP32 DevKitC-1', 30, '#1565c0')
for y, pin, color in [(310, '3V3', '#ef4444'), (385, 'GPIO 4', '#f59e0b'), (460, 'GND', '#374151')]:
    panel(draw, (145, y - 28, 410, y + 28), '#ffffff', color, 3)
    label(draw, 278, y, pin, 24, color)
panel(draw, (730, 190, 1115, 535), '#fff8e1', '#f59e0b')
label(draw, 922, 235, 'Modul DHT22 3-pin', 30, '#b45309')
for y, pin, color in [(310, 'VCC', '#ef4444'), (385, 'DATA', '#f59e0b'), (460, 'GND', '#374151')]:
    panel(draw, (790, y - 28, 1055, y + 28), '#ffffff', color, 3)
    label(draw, 922, y, pin, 24, color)
for y, color in [(310, '#ef4444'), (385, '#f59e0b'), (460, '#374151')]:
    draw.line((410, y, 790, y), fill=color, width=8)
label(draw, 600, 600, 'Jika sensor berbentuk bare 4-pin, gunakan panduan pin khususnya; jangan menebak urutan kaki.', 22, '#991b1b')
save(image, 'fs34-wiring-dht22.png')


# LAN boundary
image = Image.new('RGB', (1300, 690), '#f5f5f0')
draw = ImageDraw.Draw(image)
heading(draw, 1300, 'ESP32 tidak boleh memakai localhost PC', 'ESP32 dan PC berada di Wi-Fi yang sama, tetapi masing-masing adalah perangkat berbeda')
panel(draw, (70, 180, 500, 550), '#e3f2fd', '#1565c0')
label(draw, 285, 240, 'ESP32', 34, '#1565c0')
label(draw, 285, 330, 'MQTT_HOST =', 25)
label(draw, 285, 385, '192.168.1.23', 30, '#b45309')
label(draw, 285, 455, 'bukan 127.0.0.1', 23, '#991b1b')
panel(draw, (800, 180, 1230, 550), '#e8f5e9', '#2e7d32')
label(draw, 1015, 240, 'PC / Laptop', 34, '#2e7d32')
label(draw, 1015, 330, 'Mosquitto + MQTTX', 25)
label(draw, 1015, 385, '192.168.1.23', 30, '#b45309')
label(draw, 1015, 455, 'contoh saja', 23, '#475569')
draw.line((500, 360, 800, 360), fill='#7c3aed', width=11)
draw.polygon([(800, 360), (770, 342), (770, 378)], fill='#7c3aed')
label(draw, 650, 300, 'Wi-Fi rumah', 23, '#7c3aed')
label(draw, 650, 610, 'Ganti 192.168.1.23 dengan IPv4 PC milikmu dari ipconfig.', 24, '#334155')
save(image, 'fs34-lan-address.png')


# JSON flow
image = Image.new('RGB', (1300, 700), '#f5f5f0')
draw = ImageDraw.Draw(image)
heading(draw, 1300, 'Satu pembacaan berubah menjadi pesan JSON', 'ESP32 mengirim setiap 5 detik dengan millis; MQTTX hanya melihat hasilnya')
for bounds, title, subtitle, fill, accent in [
    ((55, 200, 350, 510), 'DHT22', '27.4 °C\n63.1 %', '#fff8e1', '#f59e0b'),
    ((502, 175, 798, 535), 'ESP32', '{"device_id":\n"esp32-meja-01",\n"temperature_c":27.4,\n"humidity_pct":63.1}', '#e3f2fd', '#1565c0'),
    ((950, 200, 1245, 510), 'MQTTX', 'topic telemetry\npesan hidup', '#e8f5e9', '#2e7d32'),
]:
    panel(draw, bounds, fill, accent)
    label(draw, (bounds[0] + bounds[2]) / 2, bounds[1] + 65, title, 31, accent)
    label(draw, (bounds[0] + bounds[2]) / 2, (bounds[1] + bounds[3]) / 2 + 35, subtitle, 20)
draw.line((350, 355, 502, 355), fill='#7c3aed', width=9)
draw.line((798, 355, 950, 355), fill='#7c3aed', width=9)
for x in (502, 950):
    draw.polygon([(x, 355), (x - 25, 338), (x - 25, 372)], fill='#7c3aed')
label(draw, 650, 615, 'Topic: kodingindonesia/fsiot/esp32-meja-01/telemetry', 24, '#334155')
save(image, 'fs34-json-flow.png')


# Troubleshooting
image = Image.new('RGB', (1300, 675), '#f5f5f0')
draw = ImageDraw.Draw(image)
heading(draw, 1300, 'Jika pesan belum muncul, cek dari yang paling dekat', 'Jangan mengubah router; periksa empat titik ini satu per satu')
checks = [
    ('1', 'DHT22', 'kabel 3V3\nGPIO 4 · GND', '#fff8e1', '#f59e0b'),
    ('2', 'Wi-Fi', 'ESP32 dan PC\njaringan sama', '#e3f2fd', '#1565c0'),
    ('3', 'Broker', 'PowerShell aktif\nIP PC benar', '#e8f5e9', '#2e7d32'),
    ('4', 'MQTTX', 'topic sama\nport 1883', '#fce7f3', '#db2777'),
]
for index, (number, title, subtitle, fill, accent) in enumerate(checks):
    left = 55 + index * 315
    panel(draw, (left, 180, left + 270, 500), fill, accent)
    label(draw, left + 135, 235, number, 28, accent)
    label(draw, left + 135, 310, title, 28)
    label(draw, left + 135, 390, subtitle, 21, '#334155')
label(draw, 650, 580, 'Jika Windows bertanya soal jaringan, izinkan hanya jaringan Private di rumah tepercaya.', 23, '#991b1b')
save(image, 'fs34-troubleshooting.png')
