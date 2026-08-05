# -*- coding: utf-8 -*-
"""Generate FS-27 cover + bus comparison + decision table diagrams."""
from PIL import Image, ImageDraw, ImageFont
from pathlib import Path

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 32)
    FH = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 20)
    F = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 16)
    FS = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 14)
    FX = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 13)
except OSError:
    FT = FH = F = FS = FX = ImageFont.load_default()


def center(d, cx, cy, text, font, fill="#1a1a1a"):
    b = d.textbbox((0, 0), text, font=font)
    d.text((cx - (b[2] - b[0]) / 2, cy - (b[3] - b[1]) / 2), text, font=font, fill=fill)


def box(d, xy, fill, outline, w=3, r=10):
    d.rounded_rectangle(xy, radius=r, fill=fill, outline=outline, width=w)


# --- Cover ---
cover = Image.new("RGB", (1200, 675), "#1B5E20")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#2E7D32", "#1B5E20", "#0D3B12"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

# three mini cards
cards = [
    (60, "#E8F5E9", "#2E7D32", "UART", "2 orang\nngobrol"),
    (420, "#E3F2FD", "#1565C0", "I2C", "banyak alat\n2 kabel"),
    (780, "#FFF8E1", "#F9A825", "SPI", "cepat ·\nlebih kabel"),
]
for x, fill, out, title, body in cards:
    box(d, (x, 90, x + 340, 380), fill, out, 4, 14)
    center(d, x + 170, 150, title, FT, out)
    for j, ln in enumerate(body.split("\n")):
        center(d, x + 170, 230 + j * 36, ln, FH, "#1a1a1a")

center(d, 600, 460, "FS-27", FT, "#C8E6C9")
center(d, 600, 530, "Bus: UART · I2C · SPI", FT, "#FFFFFF")
center(d, 600, 590, "bahasa manusia · worksheet keputusan", FS, "#E8F5E9")
center(d, 600, 640, "tanpa Upload sketch hari ini", FX, "#A5D6A7")
cover.save(OUT / "fs27-cover-bus.jpg", quality=88, optimize=True)
print("cover", (OUT / "fs27-cover-bus.jpg").stat().st_size)

# --- Main compare diagram ---
W, H = 1200, 780
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 88), "#FFFFFF", "#1a1a1a", 3)
center(d, W // 2, 40, "Gambar utama — tiga cara ngobrol antar chip (FS-27)", FT)
center(d, W // 2, 68, "Bukan “colok saja sama”: pilih bus sesuai kebutuhan · praktik di browser (worksheet)", FS, "#333")

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
        "Setiap alat punya",
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
    box(d, (x, 110, x + 360, 700), fill, out, 3)
    center(d, x + 180, 150, title, FT, out)
    for i, ln in enumerate(lines):
        d.text((x + 28, 190 + i * 34), ln, font=FS, fill="#1a1a1a")

box(d, (40, 720, W - 40, 760), "#FFFFFF", "#1a1a1a", 2)
center(d, W // 2, 740, "Intinya: UART = 1 lawan 1 · I2C = banyak alat 2 kabel + alamat · SPI = cepat tapi pin lebih banyak", FS, "#1B5E20")
img.save(OUT / "fs27-bus-compare.png", optimize=True)
print("compare", (OUT / "fs27-bus-compare.png").stat().st_size)

# --- Decision table ---
W2, H2 = 1100, 640
dec = Image.new("RGB", (W2, H2), "#F5F5F0")
d = ImageDraw.Draw(dec)
box(d, (20, 16, W2 - 20, 88), "#FFFFFF", "#1a1a1a", 3)
center(d, W2 // 2, 40, "Worksheet — kapan pakai bus apa? (FS-27)", FT)
center(d, W2 // 2, 68, "Tiga modul di jalur ini: OLED · BME280 · microSD — pilih bus sebelum wiring", FS, "#333")

# header row
headers = [("Modul", 40), ("Pilih bus", 280), ("Kenapa (bahasa manusia)", 520)]
box(d, (30, 110, W2 - 30, 160), "#ECEFF1", "#1a1a1a", 2)
for lab, x in headers:
    d.text((x, 126), lab, font=FH, fill="#1a1a1a")

rows = [
    ("OLED 0,96\"", "I2C", "Layar kecil + sensor lain\nbisa berbagi 2 kabel\n(SDA/SCL).", "#E3F2FD", "#1565C0"),
    ("BME280", "I2C", "Sensor tekanan/suhu:\nalamat di bus yang sama\ndengan OLED (FS-28).", "#E3F2FD", "#1565C0"),
    ("microSD", "SPI", "Kartu memori butuh\nkecepatan; SPI punya\nCS khusus per chip.", "#FFF8E1", "#F9A825"),
]
for i, (mod, bus, why, fill, out) in enumerate(rows):
    y0 = 170 + i * 130
    box(d, (30, y0, W2 - 30, y0 + 120), fill, out, 2)
    d.text((50, y0 + 40), mod, font=FH, fill="#1a1a1a")
    box(d, (270, y0 + 30, 470, y0 + 90), "#FFFFFF", out, 2)
    center(d, 370, y0 + 60, bus, FT, out)
    for j, ln in enumerate(why.split("\n")):
        d.text((520, y0 + 28 + j * 28), ln, font=FS, fill="#333")

box(d, (30, 570, W2 - 30, 620), "#E8F5E9", "#2E7D32", 2)
center(d, W2 // 2, 595, "UART tetap penting: Serial Monitor = jendela debug (sudah dipakai sejak FS-14) — bukan “bus sensor banyak”.", FS, "#1B5E20")
dec.save(OUT / "fs27-decision-table.png", optimize=True)
print("decision", (OUT / "fs27-decision-table.png").stat().st_size)

# --- Tools-first (browser worksheet) ---
tl = Image.new("RGB", (1100, 420), "#F5F5F0")
d = ImageDraw.Draw(tl)
box(d, (20, 16, 1080, 88), "#FFFFFF", "#1a1a1a", 3)
center(d, 550, 40, "Tools hari ini — bukan Arduino Upload", FT)
center(d, 550, 68, "FS-27 = memilih bus di kepala dulu · praktik wiring I2C di FS-28", FS, "#333")

steps = [
    ("1", "Buka artikel\ndi browser", "Baca analogi +\ntabel keputusan."),
    ("2", "Isi worksheet\nkeputusan", "Centang 10/10\ndi checklist."),
    ("3", "Siap FS-28", "Nanti: IDE +\nLibrary Manager\n+ Upload I2C."),
]
for i, (num, title, body) in enumerate(steps):
    x0 = 50 + i * 350
    fill = ["#E8F5E9", "#E3F2FD", "#FFF8E1"][i]
    out = ["#2E7D32", "#1565C0", "#F9A825"][i]
    box(d, (x0, 120, x0 + 320, 340), fill, out, 3)
    box(d, (x0 + 20, 140, x0 + 70, 190), "#FFFFFF", out, 2)
    center(d, x0 + 45, 165, num, FT, out)
    for j, ln in enumerate(title.split("\n")):
        d.text((x0 + 90, 148 + j * 26), ln, font=FH, fill="#1a1a1a")
    for j, ln in enumerate(body.split("\n")):
        d.text((x0 + 28, 230 + j * 28), ln, font=FS, fill="#333")
    if i < 2:
        center(d, x0 + 335, 230, "→", FT, "#1a1a1a")

box(d, (40, 360, 1060, 400), "#FFFFFF", "#1a1a1a", 2)
center(d, 550, 380, "Tidak perlu hari ini: Laragon · php artisan · Upload sketch · Library Manager baru", FS, "#333")
tl.save(OUT / "fs27-tools-browser.png", optimize=True)
print("tools", (OUT / "fs27-tools-browser.png").stat().st_size)
print("done")
