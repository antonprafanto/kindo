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


# Cover — broker visibly in the middle
image = Image.new('RGB', (1200, 675), '#0b347f')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 225, 1200, 675), fill='#1769c2')
for x, label, color in [(70, 'ESP32\nkirim pesan', '#fff8e1'), (780, 'HP / laptop\nlihat pesan', '#e3f2fd')]:
    box(draw, (x, 90, x + 350, 330), color, '#ffffff', 4)
    for index, line in enumerate(label.split('\n')):
        text(draw, x + 175, 165 + index * 55, line, 32, '#1f1f1f')
box(draw, (455, 115, 745, 305), '#1565c0', '#ffffff', 4)
text(draw, 600, 175, 'BROKER', 34, '#ffffff')
text(draw, 600, 230, 'perantara pesan', 22, '#e3f2fd')
arrow(draw, (420, 210), (455, 210), '#ffffff', 7, 18)
arrow(draw, (745, 210), (780, 210), '#ffffff', 7, 18)
text(draw, 600, 430, 'FS-32 · Pesan IoT tanpa bingung', 38, '#ffffff')
text(draw, 600, 500, 'Broker · topic · publish · subscribe', 26, '#e3f2fd')
text(draw, 600, 560, 'Hari ini: pahami istilah + pasang MQTTX', 22, '#dbeafe')
save(image, 'fs32-cover-mqtt.jpg')
image.save(OUT / 'fs32-cover-mqtt.webp', 'WEBP', quality=85)
print('fs32-cover-mqtt.webp', image.size)

# Roles
image = Image.new('RGB', (1200, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'MQTT: jangan kirim langsung ke semua orang', 'Semua klien berbicara kepada broker; broker meneruskan pesan menurut topic')
items = [(55, 'ESP32', 'klien\npublish', '#fff8e1', '#f9a825'), (445, 'Broker', 'petugas\npesan', '#e3f2fd', '#1565c0'), (835, 'MQTTX', 'klien\nsubscribe', '#e8f5e9', '#2e7d32')]
for x, title, body, fill, color in items:
    box(draw, (x, 190, x + 310, 490), fill, color)
    text(draw, x + 155, 265, title, 34, color)
    for index, line in enumerate(body.split('\n')):
        text(draw, x + 155, 350 + index * 44, line, 27, '#353535')
arrow(draw, (365, 340), (445, 340), '#1565c0', 8)
arrow(draw, (755, 340), (835, 340), '#2e7d32', 8)
text(draw, 600, 565, 'Klien boleh mengirim dan/atau menerima pesan. Hari ini ESP32 belum dipakai.', 22, '#353535')
save(image, 'fs32-broker-roles.png')

# Topic — four segments
image = Image.new('RGB', (1200, 640), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Topic adalah alamat pesan, bukan URL web', 'Keempat bagian dipisah garis miring; huruf besar dan huruf kecil berbeda')
box(draw, (70, 175, 1130, 285), '#1f2937', '#000000')
text(draw, 600, 230, 'kodingindonesia / fsiot / ruang-belajar / telemetry', 26, '#ffffff')
parts = [
    (80, 'kodingindonesia', 'organisasi', '#f9a825'),
    (360, 'fsiot', 'jalur belajar', '#1565c0'),
    (640, 'ruang-belajar', 'tempat', '#6a1b9a'),
    (920, 'telemetry', 'jenis pesan', '#2e7d32'),
]
for x, sample, label, color in parts:
    draw.line((x + 100, 300, x + 100, 355), fill=color, width=5)
    box(draw, (x, 355, x + 200, 500), '#ffffff', color)
    text(draw, x + 100, 405, sample, 16, color)
    text(draw, x + 100, 455, label, 20, '#303030')
text(draw, 600, 575, 'telemetry berbeda dari Telemetry. Jangan ganti nama di tengah latihan.', 22, '#b91c1c')
save(image, 'fs32-topic-address.png')

# Pub sub flow
image = Image.new('RGB', (1200, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Satu pesan dapat dilihat beberapa klien', 'Pengirim tidak perlu tahu siapa yang sedang mendengarkan')
box(draw, (55, 235, 310, 440), '#fff8e1', '#f9a825')
text(draw, 182, 295, 'ESP32', 33, '#b56d00')
text(draw, 182, 355, 'publish', 28)
box(draw, (460, 215, 740, 465), '#e3f2fd', '#1565c0')
text(draw, 600, 285, 'Broker', 36, '#1565c0')
text(draw, 600, 350, 'terima → cocokkan', 24)
text(draw, 600, 390, 'topic → teruskan', 24)
arrow(draw, (310, 338), (460, 338), '#f9a825', 8)
arrow(draw, (740, 290), (850, 290), '#2e7d32', 8)
arrow(draw, (740, 400), (850, 400), '#2e7d32', 8)
for x, top, label, sub in [(850, 205, 'MQTTX', 'subscribe'), (850, 355, 'klien lain', 'nanti, bukan hari ini')]:
    box(draw, (x, top, x + 300, top + 130), '#e8f5e9', '#2e7d32')
    text(draw, x + 150, top + 48, label, 24, '#287b35')
    text(draw, x + 150, top + 90, sub, 18, '#353535')
text(draw, 600, 560, 'Publish = kirim ke topic · subscribe = minta salinan topic itu.', 24, '#353535')
save(image, 'fs32-pub-sub-flow.png')

# Tool order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-32 — konsep dulu, siapkan klien', 'Belum mengirim pesan yang sebenarnya; lab pertama ada di FS-33')
steps = [
    ('1', 'Buka browser', 'baca konsep\nbroker + topic', '#fff8e1', '#f9a825'),
    ('2', 'Unduh MQTTX', 'halaman resmi\nmqttx.app/downloads', '#e3f2fd', '#1565c0'),
    ('3', 'Pasang aplikasi', 'ikuti pemasang\ntanpa terminal', '#e8f5e9', '#2e7d32'),
    ('4', 'Jangan connect', 'lanjut FS-33\nbroker lokal', '#fce4ec', '#c62828'),
]
for i, (number, title, body, fill, color) in enumerate(steps):
    left = 40 + i * 340
    box(draw, (left, 165, left + 310, 510), fill, color)
    box(draw, (left + 22, 188, left + 92, 258), '#ffffff', color, 3)
    text(draw, left + 57, 223, number, 32, color)
    text(draw, left + 155, 300, title, 25)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 155, 370 + line_index * 38, line, 20, '#353535')
text(draw, 700, 585, 'Tidak perlu hari ini: Arduino IDE · ESP32 · terminal · broker publik · kode program', 22, '#353535')
save(image, 'fs32-tools-order.png')

# MQTTX empty-window illustration (do not reuse official preview: it shows a public broker)
image = Image.new('RGB', (1400, 760), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'MQTTX terbuka = cukup untuk hari ini', 'Jangan isi Host dan jangan Connect. Broker lokal baru dipasang di FS-33')
box(draw, (50, 150, 1350, 620), '#1f2937', '#111827', 4)
box(draw, (50, 150, 128, 620), '#111827', '#111827', 0)
text(draw, 89, 210, 'X', 36, '#34d399')
text(draw, 89, 280, '+', 28, '#9ca3af')
box(draw, (128, 150, 430, 620), '#1f2937', '#1f2937', 0)
text(draw, 279, 200, 'Koneksi', 24, '#e5e7eb')
box(draw, (160, 250, 400, 340), '#374151', '#4b5563', 2)
text(draw, 280, 295, 'masih kosong', 20, '#d1d5db')
box(draw, (430, 150, 1350, 620), '#f8fafc', '#e5e7eb', 0)
text(draw, 890, 300, 'Belum ada koneksi', 36, '#1f2937')
text(draw, 890, 360, 'Aplikasi sudah terpasang. Berhenti di sini.', 22, '#4b5563')
box(draw, (690, 420, 1090, 500), '#fce4ec', '#c62828', 4)
text(draw, 890, 460, 'Jangan klik New Connection', 22, '#b71c1c')
text(draw, 700, 690, 'Ilustrasi jendela MQTTX buatan Koding Indonesia (FS-32), meniru tata letak aplikasi resmi EMQ.', 20, '#353535')
save(image, 'fs32-mqttx-empty.png')

# Commons architecture + Indonesian legend bar (CC BY-SA 4.0 derivative)
source = Image.open(OUT / 'fs32-mqtt-architecture-commons.png').convert('RGB')
source = source.resize((1400, int(source.height * 1400 / source.width)), Image.Resampling.LANCZOS)
legend_h = 150
cited = Image.new('RGB', (source.width, source.height + legend_h), '#f5f5f0')
cited.paste(source, (0, 0))
legend = ImageDraw.Draw(cited)
box(legend, (18, source.height + 12, source.width - 18, source.height + legend_h - 12), '#ffffff')
text(legend, source.width / 2, source.height + 48, 'Terjemahan: Publicador = pengirim · MQTT Broker = perantara · Subscritor = penerima · tópico = topic', 19)
text(legend, source.width / 2, source.height + 90, 'Ikon awan = server perantara, bukan wajib internet publik. Lab kita lebih sederhana.', 18, '#b91c1c')
cited.save(OUT / 'fs32-mqtt-architecture-cite.png', optimize=True)
print('fs32-mqtt-architecture-cite.png', cited.size)
