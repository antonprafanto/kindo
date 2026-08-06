# -*- coding: utf-8 -*-
"""Generate FS-27 cover + diagrams with large, readable text for beginners."""
from PIL import Image, ImageDraw, ImageFont
from pathlib import Path

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 48)   # title
    FH = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 34)   # heading
    FB = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 30)   # bold body
    F = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 28)     # body
    FS = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 26)    # secondary
except OSError:
    FT = FH = FB = F = FS = ImageFont.load_default()


def center(d, cx, cy, text, font, fill="#1a1a1a"):
    b = d.textbbox((0, 0), text, font=font)
    d.text((cx - (b[2] - b[0]) / 2, cy - (b[3] - b[1]) / 2), text, font=font, fill=fill)


def box(d, xy, fill, outline, w=4, r=14):
    d.rounded_rectangle(xy, radius=r, fill=fill, outline=outline, width=w)


# --- Cover ---
cover = Image.new("RGB", (1200, 675), "#1B5E20")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#2E7D32", "#1B5E20", "#0D3B12"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

cards = [
    (60, "#E8F5E9", "#2E7D32", "UART", "2 orang\nngobrol"),
    (420, "#E3F2FD", "#1565C0", "I2C", "banyak alat\n2 kabel"),
    (780, "#FFF8E1", "#F9A825", "SPI", "cepat ·\nlebih kabel"),
]
for x, fill, out, title, body in cards:
    box(d, (x, 80, x + 340, 360), fill, out, 4, 14)
    center(d, x + 170, 140, title, FT, out)
    for j, ln in enumerate(body.split("\n")):
        center(d, x + 170, 220 + j * 40, ln, FH, "#1a1a1a")

center(d, 600, 430, "FS-27", FT, "#C8E6C9")
center(d, 600, 500, "Bus: UART · I2C · SPI", FT, "#FFFFFF")
center(d, 600, 565, "bahasa manusia · worksheet keputusan", F, "#E8F5E9")
center(d, 600, 620, "tanpa Upload sketch hari ini", FS, "#A5D6A7")
cover.save(OUT / "fs27-cover-bus.jpg", quality=88, optimize=True)
cover.save(OUT / "fs27-cover-bus.webp", "WEBP", quality=85)
print("cover", (OUT / "fs27-cover-bus.jpg").stat().st_size)

# --- Main compare (taller for larger text) ---
W, H = 1200, 860
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 42, "Gambar utama — tiga cara ngobrol antar chip (FS-27)", FT)
center(d, W // 2, 78, "Pilih bus sebelum merakit · praktik di browser (worksheet)", F, "#333")

cols = [
    (40, "#E8F5E9", "#2E7D32", "UART", [
        "Analogi: 2 orang",
        "telepon langsung",
        "",
        "Kabel tipikal:",
        "TX · RX · GND",
        "",
        "Sudah kamu pakai:",
        "Serial Monitor",
        "(USB = UART)",
        "",
        "Cocok: debug,",
        "GPS, modul 1↔1",
    ]),
    (420, "#E3F2FD", "#1565C0", "I2C", [
        "Analogi: rapat",
        "banyak orang,",
        "panggil nama",
        "",
        "Kabel tipikal:",
        "SDA · SCL ·",
        "VCC · GND",
        "",
        "Tiap alat punya",
        "alamat (mis. 0x76)",
        "",
        "Cocok: BME280,",
        "OLED, sensor banyak",
    ]),
    (800, "#FFF8E1", "#F9A825", "SPI", [
        "Analogi: kurir",
        "cepat + jalur",
        "khusus per paket",
        "",
        "Kabel tipikal:",
        "SCK · MOSI ·",
        "MISO · CS (+GND)",
        "",
        "Lebih cepat,",
        "lebih banyak pin",
        "",
        "Cocok: microSD,",
        "layar cepat, flash",
    ]),
]
for x, fill, out, title, lines in cols:
    box(d, (x, 120, x + 360, 760), fill, out, 4)
    center(d, x + 180, 165, title, FT, out)
    for i, ln in enumerate(lines):
        d.text((x + 36, 210 + i * 38), ln, font=F, fill="#1a1a1a")

box(d, (40, 780, W - 40, 840), "#FFFFFF", "#1a1a1a", 3)
center(
    d,
    W // 2,
    810,
    "UART = 1 lawan 1 · I2C = banyak alat + alamat · SPI = cepat, pin lebih banyak",
    F,
    "#1B5E20",
)
img.save(OUT / "fs27-bus-compare.png", optimize=True)
print("compare", (OUT / "fs27-bus-compare.png").stat().st_size)

# --- Decision table (larger) ---
W2, H2 = 1200, 720
dec = Image.new("RGB", (W2, H2), "#F5F5F0")
d = ImageDraw.Draw(dec)
box(d, (20, 16, W2 - 20, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, W2 // 2, 42, "Worksheet — kapan pakai bus apa? (FS-27)", FT)
center(d, W2 // 2, 78, "OLED · BME280 · microSD — pilih bus sebelum wiring", F, "#333")

box(d, (30, 120, W2 - 30, 180), "#ECEFF1", "#1a1a1a", 3)
d.text((55, 138), "Modul", font=FH, fill="#1a1a1a")
d.text((320, 138), "Pilih bus", font=FH, fill="#1a1a1a")
d.text((560, 138), "Kenapa (bahasa manusia)", font=FH, fill="#1a1a1a")

rows = [
    ("OLED 0,96\"", "I2C", "Layar kecil + sensor lain\nbisa berbagi 2 kabel\n(SDA / SCL).", "#E3F2FD", "#1565C0"),
    ("BME280", "I2C", "Sensor tekanan/suhu:\nalamat di bus yang sama\ndengan OLED (FS-28).", "#E3F2FD", "#1565C0"),
    ("microSD", "SPI", "Kartu memori butuh\nkecepatan; SPI punya\nCS khusus per chip.", "#FFF8E1", "#F9A825"),
]
for i, (mod, bus, why, fill, out) in enumerate(rows):
    y0 = 195 + i * 140
    box(d, (30, y0, W2 - 30, y0 + 128), fill, out, 3)
    d.text((55, y0 + 48), mod, font=FH, fill="#1a1a1a")
    box(d, (300, y0 + 30, 500, y0 + 100), "#FFFFFF", out, 3)
    center(d, 400, y0 + 65, bus, FT, out)
    for j, ln in enumerate(why.split("\n")):
        d.text((560, y0 + 28 + j * 30), ln, font=F, fill="#333")

box(d, (30, 620, W2 - 30, 690), "#E8F5E9", "#2E7D32", 3)
center(
    d,
    W2 // 2,
    655,
    "UART tetap penting: Serial Monitor = jendela debug (FS-14) — bukan “bus sensor banyak”.",
    F,
    "#1B5E20",
)
dec.save(OUT / "fs27-decision-table.png", optimize=True)
print("decision", (OUT / "fs27-decision-table.png").stat().st_size)

# --- Tools-first (large readable text) ---
LM_W, LM_H = 1400, 720
tl = Image.new("RGB", (LM_W, LM_H), "#F5F5F0")
d = ImageDraw.Draw(tl)
box(d, (20, 16, LM_W - 20, 140), "#FFFFFF", "#1a1a1a", 4)
center(d, LM_W // 2, 55, "Tools hari ini — bukan Arduino Upload", FT)
center(d, LM_W // 2, 108, "FS-27 = pilih bus di kepala dulu · praktik wiring I2C di FS-28", FB, "#333")

steps = [
    ("1", "Buka artikel\ndi browser", "Baca analogi +\ntabel keputusan.", "#E8F5E9", "#2E7D32"),
    ("2", "Isi worksheet\nkeputusan", "Centang 10/10\ndi checklist.", "#E3F2FD", "#1565C0"),
    ("3", "Siap FS-28", "Nanti: IDE +\nLibrary Manager\n+ Upload I2C.", "#FFF8E1", "#F9A825"),
]
for i, (num, title, body, fill, out) in enumerate(steps):
    x0 = 40 + i * 450
    box(d, (x0, 170, x0 + 420, 520), fill, out, 4)
    box(d, (x0 + 28, 195, x0 + 108, 275), "#FFFFFF", out, 3)
    center(d, x0 + 68, 235, num, FT, out)
    for j, ln in enumerate(title.split("\n")):
        d.text((x0 + 128, 205 + j * 42), ln, font=FH, fill="#1a1a1a")
    for j, ln in enumerate(body.split("\n")):
        d.text((x0 + 36, 340 + j * 40), ln, font=F, fill="#333")
    if i < 2:
        center(d, x0 + 435, 345, "→", FT, "#1a1a1a")

box(d, (40, 555, LM_W - 40, 690), "#FFFFFF", "#1a1a1a", 3)
center(d, LM_W // 2, 600, "Tidak perlu hari ini:", FH, "#1a1a1a")
center(d, LM_W // 2, 655, "Laragon  ·  php artisan  ·  Upload sketch  ·  Library Manager baru", FB, "#333")
tl.save(OUT / "fs27-tools-browser.png", optimize=True)
print("tools", (OUT / "fs27-tools-browser.png").stat().st_size)

# --- I2C labeled ---
W, H = 1200, 580
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 42, "I2C — banyak perangkat di 2 kabel (SDA + SCL)", FT)
center(d, W // 2, 78, "ESP32 = pengendali · tiap alat punya alamat · pin jalur: SDA 21 · SCL 22", F, "#333")

d.line([(100, 210), (1100, 210)], fill="#1565C0", width=7)
d.line([(100, 280), (1100, 280)], fill="#2E7D32", width=7)
d.text((40, 190), "SDA", font=FH, fill="#1565C0")
d.text((40, 260), "SCL", font=FH, fill="#2E7D32")

nodes = [
    (200, "ESP32", "pengendali", "#FFECB3", "#F9A825"),
    (480, "BME280", "alamat 0x76", "#BBDEFB", "#1565C0"),
    (760, "OLED", "alamat 0x3C", "#C8E6C9", "#2E7D32"),
    (1020, "(nanti)", "perangkat lain", "#E0E0E0", "#616161"),
]
for x, title, sub, fill, out in nodes:
    box(d, (x - 100, 330, x + 100, 460), fill, out, 4)
    center(d, x, 375, title, FH, out)
    center(d, x, 420, sub, F, "#333")
    d.line([(x, 330), (x, 280)], fill="#2E7D32", width=5)
    d.line([(x, 330), (x, 210)], fill="#1565C0", width=5)

box(d, (40, 490, W - 40, 555), "#E3F2FD", "#1565C0", 3)
center(d, W // 2, 522, "2 kabel bersama + panggil alamat · inspirasi Commons I2C.svg · Koding Indonesia (FS-27)", F, "#0D47A1")
img.save(OUT / "fs27-i2c-labeled.png", optimize=True)
print("i2c", (OUT / "fs27-i2c-labeled.png").stat().st_size)

# --- SPI labeled ---
W, H = 1200, 580
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 42, "SPI — cepat, tapi pin lebih banyak (CS per chip)", FT)
center(d, W // 2, 78, "CS = Chip Select (di buku lama sering tertulis SS) · cocok microSD", F, "#333")

box(d, (80, 140, 460, 450), "#FFF8E1", "#F9A825", 4)
center(d, 270, 185, "ESP32 (pengendali)", FH, "#F57F17")
for i, (lab, col) in enumerate(
    [("SCK  — jam", "#455A64"), ("MOSI — keluar", "#1565C0"), ("MISO — masuk", "#2E7D32"), ("CS   — pilih chip", "#C62828")]
):
    y = 230 + i * 48
    box(d, (110, y, 430, y + 40), "#FFFFFF", col, 3)
    center(d, 270, y + 20, lab, F, col)

box(d, (740, 140, 1120, 450), "#E8F5E9", "#2E7D32", 4)
center(d, 930, 185, "microSD (perangkat)", FH, "#1B5E20")
for i, (lab, col) in enumerate(
    [("SCK", "#455A64"), ("MOSI", "#1565C0"), ("MISO", "#2E7D32"), ("CS", "#C62828")]
):
    y = 230 + i * 48
    box(d, (770, y, 1090, y + 40), "#FFFFFF", col, 3)
    center(d, 930, y + 20, lab, F, col)

for i, col in enumerate(["#455A64", "#1565C0", "#2E7D32", "#C62828"]):
    y = 250 + i * 48
    d.line([(460, y), (740, y)], fill=col, width=5)
    d.polygon([(740, y), (715, y - 10), (715, y + 10)], fill=col)

box(d, (40, 480, W - 40, 550), "#FFF8E1", "#F9A825", 3)
center(d, W // 2, 515, "Tiap perangkat SPI butuh CS sendiri · inspirasi Commons SPI_single_slave.svg · Koding Indonesia (FS-27)", F, "#E65100")
img.save(OUT / "fs27-spi-labeled.png", optimize=True)
print("spi", (OUT / "fs27-spi-labeled.png").stat().st_size)

# Update seeder figure dimensions for tools/compare if needed — keep max-height CSS
print("done")
