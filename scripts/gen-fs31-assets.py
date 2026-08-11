# -*- coding: utf-8 -*-
"""Generate FS-31 / #101 diagrams for local ESP32 web monitoring."""
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)


def font(size: int):
    try:
        return ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", size)
    except OSError:
        return ImageFont.load_default()


TITLE = font(44)
HEADING = font(32)
BODY = font(27)
SMALL = font(22)


def text(draw, x, y, value, selected_font=BODY, fill="#1a1a1a"):
    draw.text((x, y), value, font=selected_font, fill=fill, anchor="mm")


def box(draw, coords, fill="#FFFFFF", outline="#1a1a1a", width=4, radius=14):
    draw.rounded_rectangle(coords, radius=radius, fill=fill, outline=outline, width=width)


def header(draw, width, title, subtitle):
    box(draw, (20, 16, width - 20, 120))
    title_font = TITLE if len(title) <= 38 else font(36)
    text(draw, width // 2, 48, title, title_font)
    text(draw, width // 2, 92, subtitle, SMALL, "#333333")


def save(image, name):
    image.save(OUT / name, optimize=True)
    print(name, (OUT / name).stat().st_size)


# Cover
image = Image.new("RGB", (1200, 675), "#0D47A1")
draw = ImageDraw.Draw(image)
for top, color in [(0, "#002171"), (225, "#0D47A1"), (450, "#1565C0")]:
    draw.rectangle((0, top, 1200, top + 225), fill=color)
box(draw, (70, 72, 505, 350), "#FFF8E1", "#FFFFFF")
text(draw, 288, 145, "ESP32 + DHT22", HEADING, "#E65100")
text(draw, 288, 215, "baca suhu", BODY)
text(draw, 288, 275, "jadi halaman web", BODY)
box(draw, (695, 72, 1130, 350), "#E3F2FD", "#FFFFFF")
text(draw, 912, 145, "HP / laptop", HEADING, "#0D47A1")
text(draw, 912, 215, "buka IP", BODY)
text(draw, 912, 275, "di browser", BODY)
text(draw, 600, 430, "FS-31", TITLE, "#BBDEFB")
text(draw, 600, 500, "Web server lokal ESP32", TITLE, "#FFFFFF")
text(draw, 600, 560, "Pantau suhu di browser · satu Wi-Fi · tanpa aplikasi", HEADING, "#E3F2FD")
text(draw, 600, 625, "Arduino IDE · Upload · Serial 115200 · alamat IP", BODY, "#90CAF9")
image.save(OUT / "fs31-cover-web-server.jpg", quality=88, optimize=True)
image.save(OUT / "fs31-cover-web-server.webp", "WEBP", quality=85)
print("cover done")

# Tool order
image = Image.new("RGB", (1400, 720), "#F5F5F0")
draw = ImageDraw.Draw(image)
header(draw, 1400, "Urutan tools hari ini — mulai dari IDE", "Jangan ketik IP sebelum Serial Monitor memberikannya")
steps = [
    ("1", "Siapkan DHT22", "ESP32 + USB data\n+Wi-Fi 2,4 GHz", "#FFF8E1", "#F9A825"),
    ("2", "Buka Arduino IDE", "Pilih board + port\n+isi SSID/sandi", "#E3F2FD", "#1565C0"),
    ("3", "Upload & Serial", "Verify → Upload\n+salin alamat IP", "#E8F5E9", "#2E7D32"),
    ("4", "Buka browser", "http://IP/\n+lihat suhu", "#FCE4EC", "#C62828"),
]
for index, (number, title, body, fill, color) in enumerate(steps):
    left = 40 + index * 340
    box(draw, (left, 170, left + 310, 520), fill, color)
    box(draw, (left + 20, 195, left + 92, 267), "#FFFFFF", color, 3)
    text(draw, left + 56, 231, number, TITLE, color)
    text(draw, left + 155, 300, title, HEADING)
    for line_index, line in enumerate(body.split("\n")):
        text(draw, left + 155, 380 + line_index * 42, line, BODY, "#333333")
box(draw, (40, 555, 1360, 690))
text(draw, 700, 603, "Tidak perlu hari ini:", HEADING)
text(draw, 700, 655, "Laragon · php artisan · aplikasi dari app store · port forwarding · MQTT", BODY, "#333333")
save(image, "fs31-tools-order.png")

# WebServer core
image = Image.new("RGB", (1200, 560), "#F5F5F0")
draw = ImageDraw.Draw(image)
header(draw, 1200, "WebServer.h sudah ada di core ESP32", "Tidak ada Library Manager baru untuk server web kecil ini")
steps = [
    ("1", "Buat server", "WebServer server(80);", "#E3F2FD", "#1565C0"),
    ("2", "Tentukan halaman", "server.on(\"/\", ...);", "#FFF8E1", "#F9A825"),
    ("3", "Layani browser", "server.handleClient();", "#E8F5E9", "#2E7D32"),
]
for index, (number, title, code, fill, color) in enumerate(steps):
    left = 40 + index * 390
    box(draw, (left, 155, left + 360, 475), fill, color)
    text(draw, left + 180, 215, number + " · " + title, HEADING, color)
    box(draw, (left + 25, 295, left + 335, 400), "#263238", "#1a1a1a", 3)
    text(draw, left + 180, 347, code, SMALL, "#E0F2F1")
save(image, "fs31-webserver-core.png")

# Main network figure
image = Image.new("RGB", (1200, 720), "#F5F5F0")
draw = ImageDraw.Draw(image)
header(draw, 1200, "Gambar utama — browser meminta halaman dari ESP32", "Satu Wi-Fi lokal · browser memulai permintaan · ESP32 menjawab HTML")
items = [
    ("ESP32 + DHT22", "server kecil\nIP 192.168.1.42", "#FFF8E1", "#F9A825", 70),
    ("Router Wi-Fi", "jaringan sama\n2,4 GHz", "#E3F2FD", "#1565C0", 440),
    ("HP / laptop", "browser buka\nhttp://192.168.1.42/", "#E8F5E9", "#2E7D32", 820),
]
for title, body, fill, color, left in items:
    box(draw, (left, 190, left + 310, 465), fill, color)
    text(draw, left + 155, 260, title, HEADING, color)
    for line_index, line in enumerate(body.split("\n")):
        text(draw, left + 155, 335 + line_index * 42, line, BODY)
draw.line([(380, 327), (440, 327)], fill="#1565C0", width=8)
draw.line([(750, 327), (820, 327)], fill="#2E7D32", width=8)
text(draw, 410, 290, "Wi-Fi", SMALL, "#1565C0")
text(draw, 785, 290, "HTTP", SMALL, "#2E7D32")
box(draw, (55, 535, 1145, 665), "#FFFFFF", "#1a1a1a", 3)
text(draw, 600, 580, "Bukan internet publik: halaman hanya ada di jaringan rumah / hotspot yang sama.", BODY)
text(draw, 600, 630, "Jangan pakai localhost di HP — itu menunjuk ke HP sendiri, bukan ESP32.", BODY, "#B71C1C")
save(image, "fs31-local-network.png")

# Refresh figure
image = Image.new("RGB", (1200, 620), "#F5F5F0")
draw = ImageDraw.Draw(image)
header(draw, 1200, "Refresh sederhana — halaman meminta angka terbaru", "Versi pertama yang mudah dilihat; belum memakai aplikasi atau database")
for top, number, title, detail, fill, color in [
    (160, "1", "Browser buka /", "ESP32 membaca nilai suhu terakhir", "#E3F2FD", "#1565C0"),
    (315, "2", "ESP32 kirim HTML", "Halaman menunjukkan suhu + kelembapan", "#FFF8E1", "#F9A825"),
    (470, "3", "Browser muat ulang", "Otomatis setiap 5 detik atau tekan refresh", "#E8F5E9", "#2E7D32"),
]:
    box(draw, (70, top, 1130, top + 110), fill, color)
    box(draw, (95, top + 20, 165, top + 90), "#FFFFFF", color, 3)
    text(draw, 130, top + 55, number, HEADING, color)
    text(draw, 370, top + 42, title, HEADING)
    text(draw, 700, top + 75, detail, BODY, "#333333")
save(image, "fs31-refresh-flow.png")

# Browser success
image = Image.new("RGB", (1200, 520), "#F5F5F0")
draw = ImageDraw.Draw(image)
header(draw, 1200, "Sukses — suhu tampil di browser", "Contoh tampilan; angka asli bergantung sensor dan ruanganmu")
box(draw, (60, 150, 1140, 465), "#FFFFFF", "#1a1a1a", 4)
box(draw, (80, 175, 1120, 235), "#E3F2FD", "#1565C0", 3)
text(draw, 600, 205, "http://192.168.1.42/", BODY, "#0D47A1")
text(draw, 600, 295, "Stasiun Ruang Belajar Kindo", HEADING, "#0D47A1")
text(draw, 600, 365, "Suhu: 28.4 °C", TITLE, "#E65100")
text(draw, 600, 420, "Halaman diperbarui tiap 5 detik", SMALL, "#333333")
save(image, "fs31-success-browser.png")

# Troubleshooting figure
image = Image.new("RGB", (1200, 590), "#F5F5F0")
draw = ImageDraw.Draw(image)
header(draw, 1200, "Jika halaman tidak terbuka — cek dari yang paling dekat", "Baca Serial dulu; jangan langsung mengubah banyak hal sekaligus")
checks = [
    ("1", "Serial punya IP?", ["Jika belum ada IP,", "selesaikan Wi-Fi lebih dulu."], "#FFF8E1", "#F9A825"),
    ("2", "Wi-Fi sama?", ["HP/laptop dan ESP32 harus satu", "jaringan, bukan guest Wi-Fi."], "#E3F2FD", "#1565C0"),
    ("3", "Alamat benar?", ["Ketik http:// lalu IP dari Serial", "di kolom alamat browser."], "#E8F5E9", "#2E7D32"),
]
for index, (number, title, detail, fill, color) in enumerate(checks):
    left = 40 + index * 390
    box(draw, (left, 150, left + 360, 500), fill, color)
    text(draw, left + 180, 215, number + " · " + title, HEADING, color)
    for line_index, line in enumerate(detail):
        text(draw, left + 180, 330 + line_index * 42, line, SMALL, "#333333")
save(image, "fs31-troubleshooting.png")

print("done")
