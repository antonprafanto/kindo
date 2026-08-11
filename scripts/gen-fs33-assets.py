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
image = Image.new('RGB', (1200, 675), '#12315c')
draw = ImageDraw.Draw(image)
draw.rectangle((0, 255, 1200, 675), fill='#1d64b8')
box(draw, (120, 85, 480, 390), '#e3f2fd', '#ffffff')
text(draw, 300, 170, 'MQTTX', 40, '#1565c0')
text(draw, 300, 240, 'kirim / lihat', 30)
text(draw, 300, 290, 'pesan latihan', 30)
box(draw, (720, 85, 1080, 390), '#e8f5e9', '#ffffff')
text(draw, 900, 170, 'Mosquitto', 40, '#2e7d32')
text(draw, 900, 240, 'broker lokal', 30)
text(draw, 900, 290, 'di komputer sendiri', 30)
draw.line((480, 238, 720, 238), fill='#ffd54f', width=12)
text(draw, 600, 480, '127.0.0.1 : 1883', 34, '#fff3b0')
text(draw, 600, 550, 'FS-33 · Pesan MQTT pertama tanpa internet publik', 34, '#ffffff')
save(image, 'fs33-cover-mosquitto.jpg')
image.save(OUT / 'fs33-cover-mosquitto.webp', 'WEBP', quality=85)

# Tools order
image = Image.new('RGB', (1400, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1400, 'Urutan tools FS-33 — broker lokal dulu', 'Satu komputer saja sudah cukup; ESP32 menyusul pada FS-34')
steps = [('1', 'Buka browser', 'unduh Mosquitto\ndari situs resmi', '#fff8e1', '#f9a825'), ('2', 'Pasang Mosquitto', 'ikuti installer\nWindows/macOS/Linux', '#e3f2fd', '#1565c0'), ('3', 'Buka PowerShell', 'jalankan broker\ntetap terbuka', '#e8f5e9', '#2e7d32'), ('4', 'Buka MQTTX', 'koneksi 127.0.0.1\nkirim pesan', '#fce4ec', '#c62828')]
for index, (number, title, body, fill, color) in enumerate(steps):
    left = 40 + index * 340
    box(draw, (left, 165, left + 310, 510), fill, color)
    box(draw, (left + 22, 188, left + 92, 258), '#ffffff', color, 3)
    text(draw, left + 57, 223, number, 32, color)
    text(draw, left + 155, 300, title, 24)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 155, 370 + line_index * 38, line, 21, '#353535')
text(draw, 700, 585, 'Belum perlu: ESP32 · Arduino IDE · IP router · firewall · broker publik', 23, '#353535')
save(image, 'fs33-tools-order.png')

# Local boundary
image = Image.new('RGB', (1200, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Lab ini hanya berputar di komputer sendiri', '127.0.0.1 berarti "komputer ini"; pesan tidak keluar ke internet')
draw.rounded_rectangle((95, 165, 1105, 515), radius=28, fill='#e3f2fd', outline='#1565c0', width=5)
text(draw, 600, 205, 'KOMPUTER KAMU', 27, '#1565c0')
box(draw, (180, 275, 440, 445), '#ffffff', '#2e7d32')
text(draw, 310, 330, 'MQTTX', 33, '#2e7d32')
text(draw, 310, 385, 'client', 25)
box(draw, (760, 250, 1020, 470), '#e8f5e9', '#2e7d32')
text(draw, 890, 320, 'Mosquitto', 32, '#2e7d32')
text(draw, 890, 365, 'broker', 27)
text(draw, 890, 410, 'port 1883', 22)
draw.line((440, 360, 760, 360), fill='#1565c0', width=10)
text(draw, 600, 575, 'Jangan buka port router atau firewall untuk praktik satu komputer ini.', 23, '#b91c1c')
save(image, 'fs33-local-only.png')

# First message
image = Image.new('RGB', (1200, 650), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Pesan pertama: publisher → broker → subscriber', 'Buat subscription dulu, lalu kirim ke topic latihan yang sama')
box(draw, (60, 205, 340, 455), '#e3f2fd', '#1565c0')
text(draw, 200, 275, 'MQTTX', 35, '#1565c0')
text(draw, 200, 335, '3. menerima', 25)
text(draw, 200, 380, 'topic chat', 25)
box(draw, (460, 185, 740, 475), '#e8f5e9', '#2e7d32')
text(draw, 600, 265, 'Mosquitto', 35, '#2e7d32')
text(draw, 600, 330, '2. menerima', 25)
text(draw, 600, 375, 'dan teruskan', 25)
box(draw, (860, 205, 1140, 455), '#fff8e1', '#f9a825')
text(draw, 1000, 275, 'MQTTX', 35, '#b56d00')
text(draw, 1000, 335, '1. publish', 25)
text(draw, 1000, 380, 'halo dari PC saya', 20)
draw.line((740, 370, 860, 370), fill='#f9a825', width=8)
draw.polygon([(740, 370), (765, 355), (765, 385)], fill='#f9a825')
draw.line((340, 330, 460, 330), fill='#1565c0', width=8)
draw.polygon([(340, 330), (365, 315), (365, 345)], fill='#1565c0')
text(draw, 600, 560, 'Pesan terlihat di MQTTX; itu bukti broker lokal bekerja.', 24, '#353535')
save(image, 'fs33-first-message.png')

# Port diagnosis
image = Image.new('RGB', (1200, 620), '#f5f5f0')
draw = ImageDraw.Draw(image)
header(draw, 1200, 'Jika tidak tersambung, cek tiga hal ini', 'Mulai dari broker, lalu alamat, baru port')
items = [('1', 'Jendela broker', 'masih terbuka\ndan tidak error', '#fff8e1', '#f9a825'), ('2', 'Alamat', 'pakai 127.0.0.1\nbukan IP router', '#e3f2fd', '#1565c0'), ('3', 'Port', 'isi 1883\ntanpa HTTPS', '#e8f5e9', '#2e7d32')]
for index, (number, title, body, fill, color) in enumerate(items):
    left = 85 + index * 370
    box(draw, (left, 175, left + 300, 475), fill, color)
    text(draw, left + 150, 235, number, 30, color)
    text(draw, left + 150, 300, title, 27)
    for line_index, line in enumerate(body.split('\n')):
        text(draw, left + 150, 365 + line_index * 38, line, 22, '#353535')
text(draw, 600, 555, 'Untuk lab lokal, jangan mengubah firewall atau konfigurasi listener.', 23, '#b91c1c')
save(image, 'fs33-troubleshooting.png')
