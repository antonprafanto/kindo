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


def save(image, name):
    image.save(OUT / name, optimize=True)
    print(name)


# Cover
image = Image.new('RGB', (1200, 675), '#0b347f')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 225, 1200, 675), fill='#1769c2')
for x, label, color in [(85, 'ESP32\nkirim pesan', '#fff8e1'), (710, 'HP / laptop\nterima pesan', '#e3f2fd')]:
    box(draw, (x, 75, x + 405, 355), color, '#ffffff', 4)
    for index, line in enumerate(label.split('\n')):
        text(draw, x + 202, 160 + index * 72, line, 35, '#1f1f1f')
text(draw, 600, 450, 'BROKER MQTT', 31, '#b8dcff')
text(draw, 600, 520, 'FS-32 · Pesan IoT tanpa bingung', 39, '#ffffff')
text(draw, 600, 585, 'Broker · topic · publish · subscribe', 27, '#e3f2fd')
save(image, 'fs32-cover-mqtt.jpg')
image.save(OUT / 'fs32-cover-mqtt.webp', 'WEBP', quality=85)

# Roles
image = Image.new('RGB', (1200, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'MQTT: jangan kirim langsung ke semua orang', 'Semua client berbicara kepada broker; broker meneruskan pesan menurut topic')
items = [(55, 'ESP32', 'client\npublish', '#fff8e1', '#f9a825'), (445, 'Broker', 'petugas\npesan', '#e3f2fd', '#1565c0'), (835, 'MQTTX', 'client\nsubscribe', '#e8f5e9', '#2e7d32')]
for x, title, body, fill, color in items:
    box(draw, (x, 190, x + 310, 490), fill, color)
    text(draw, x + 155, 265, title, 34, color)
    for index, line in enumerate(body.split('\n')):
        text(draw, x + 155, 350 + index * 44, line, 27, '#353535')
draw.line((365, 340, 445, 340), fill='#1565c0', width=8)
draw.line((755, 340, 835, 340), fill='#2e7d32', width=8)
text(draw, 600, 565, 'Client boleh mengirim dan/atau menerima pesan.', 25, '#353535')
save(image, 'fs32-broker-roles.png')

# Topic flow
image = Image.new('RGB', (1200, 620), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Topic adalah alamat pesan, bukan URL web', 'Gunakan nama yang konsisten agar publisher dan subscriber bertemu di tempat yang sama')
box(draw, (90, 185, 1110, 300), '#1f2937', '#000000')
text(draw, 600, 242, 'kodingindonesia/fsiot/ruang-belajar/telemetry', 29, '#ffffff')
for x, label, color in [(150, 'nama organisasi', '#f9a825'), (440, 'jalur proyek', '#1565c0'), (750, 'jenis pesan', '#2e7d32')]:
    draw.line((x + 110, 335, x + 110, 390), fill=color, width=5)
    box(draw, (x, 390, x + 220, 510), '#ffffff', color)
    text(draw, x + 110, 450, label, 20, '#303030')
text(draw, 600, 565, 'Huruf besar/kecil berbeda: telemetry ≠ Telemetry.', 23, '#b91c1c')
save(image, 'fs32-topic-address.png')

# Pub sub flow
image = Image.new('RGB', (1200, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Satu pesan dapat dilihat beberapa client', 'Publisher tidak perlu tahu siapa yang sedang mendengarkan')
box(draw, (55, 235, 310, 440), '#fff8e1', '#f9a825')
text(draw, 182, 295, 'ESP32', 33, '#b56d00')
text(draw, 182, 355, 'publish', 28)
box(draw, (460, 215, 740, 465), '#e3f2fd', '#1565c0')
text(draw, 600, 285, 'Broker', 36, '#1565c0')
text(draw, 600, 350, 'terima → cocokkan', 24)
text(draw, 600, 390, 'topic → teruskan', 24)
draw.line((310, 338, 460, 338), fill='#f9a825', width=8)
draw.line((740, 290, 850, 290), fill='#2e7d32', width=8)
draw.line((740, 400, 1010, 400), fill='#2e7d32', width=8)
for x, top, label in [(850, 205, 'MQTTX'), (1010, 330, 'dashboard')]:
    box(draw, (x, top, x + 150, top + 145), '#e8f5e9', '#2e7d32')
    text(draw, x + 75, top + 58, label, 20, '#287b35')
    text(draw, x + 75, top + 98, 'subscribe', 18)
text(draw, 600, 560, 'Publish = kirim ke topic · subscribe = minta salinan topic.', 24, '#353535')
save(image, 'fs32-pub-sub-flow.png')

# Tool order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-32 — konsep dulu, siapkan client', 'Belum mengirim pesan sungguhan hari ini; lab pertama ada di FS-33')
steps = [('1', 'Buka browser', 'baca konsep\nbroker + topic', '#fff8e1', '#f9a825'), ('2', 'Unduh MQTTX', 'pilih installer\nsesuai OS', '#e3f2fd', '#1565c0'), ('3', 'Pasang aplikasi', 'ikuti installer\ntanpa terminal', '#e8f5e9', '#2e7d32'), ('4', 'Jangan connect dulu', 'lanjut FS-33\nbroker lokal', '#fce4ec', '#c62828')]
for i, (number, title, body, fill, color) in enumerate(steps):
    left = 40 + i * 340
    box(draw, (left, 165, left + 310, 510), fill, color)
    box(draw, (left + 22, 188, left + 92, 258), '#ffffff', color, 3)
    text(draw, left + 57, 223, number, 32, color)
    text(draw, left + 155, 300, title, 25)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 155, 370 + line_index * 38, line, 22, '#353535')
text(draw, 700, 585, 'Tidak perlu hari ini: Arduino IDE · ESP32 · broker publik · kode program', 24, '#353535')
save(image, 'fs32-tools-order.png')
