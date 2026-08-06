# -*- coding: utf-8 -*-
"""Generate FS-27 cover + diagrams with large, readable text for beginners."""
from PIL import Image, ImageDraw, ImageFont
from pathlib import Path

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)
TMP = Path("tmp-qa97")

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 44)   # title
    FH = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 32)   # heading
    FB = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 28)   # bold body
    F = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 26)     # body
    FS = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 24)    # secondary
except OSError:
    FT = FH = FB = F = FS = ImageFont.load_default()


def center(d, cx, cy, text, font, fill="#1a1a1a"):
    """True geometric + slight optical nudge for Segoe on raster badges."""
    # Thin digit "1" looks right-heavy; shift left a hair.
    ox = -1.0 if len(text) == 1 and text.isdigit() else 0
    # Caps with anchor=mm still read ~1px low on these pill heights.
    d.text((cx + ox, cy - 1), text, font=font, fill=fill, anchor="mm")


def box(d, xy, fill, outline, w=4, r=14):
    d.rounded_rectangle(xy, radius=r, fill=fill, outline=outline, width=w)


def fit_cover(src: Path, size=(340, 260)) -> Image.Image:
    im = Image.open(src).convert("RGB")
    im.thumbnail(size, Image.Resampling.LANCZOS)
    canvas = Image.new("RGB", size, "#FFFFFF")
    canvas.paste(im, ((size[0] - im.width) // 2, (size[1] - im.height) // 2))
    return canvas


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
    box(d, (x, 60, x + 340, 320), fill, out, 4, 14)
    center(d, x + 170, 120, title, FT, out)
    for j, ln in enumerate(body.split("\n")):
        center(d, x + 170, 195 + j * 42, ln, FH, "#1a1a1a")

center(d, 600, 390, "FS-27", FT, "#C8E6C9")
center(d, 600, 460, "Bus: UART · I2C · SPI", FT, "#FFFFFF")
center(d, 600, 535, "bahasa manusia · worksheet keputusan", FB, "#E8F5E9")
center(d, 600, 600, "tanpa Upload sketch hari ini", FH, "#C8E6C9")
cover.save(OUT / "fs27-cover-bus.jpg", quality=88, optimize=True)
cover.save(OUT / "fs27-cover-bus.webp", "WEBP", quality=85)
print("cover", (OUT / "fs27-cover-bus.jpg").stat().st_size)

# --- Main compare ---
W, H = 1200, 920
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 130), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 50, "Gambar utama — tiga cara ngobrol antar chip (FS-27)", FT)
center(d, W // 2, 100, "Pilih bus sebelum merakit · praktik di browser (worksheet)", F, "#333")

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
    box(d, (x, 150, x + 360, 800), fill, out, 4)
    center(d, x + 180, 195, title, FT, out)
    for i, ln in enumerate(lines):
        d.text((x + 36, 245 + i * 38), ln, font=F, fill="#1a1a1a")

box(d, (40, 825, W - 40, 895), "#FFFFFF", "#1a1a1a", 3)
center(
    d,
    W // 2,
    860,
    "UART = 1 lawan 1 · I2C = banyak alat + alamat · SPI = cepat, pin lebih banyak",
    F,
    "#1B5E20",
)
img.save(OUT / "fs27-bus-compare.png", optimize=True)
print("compare", (OUT / "fs27-bus-compare.png").stat().st_size)

# --- Decision table ---
W2, H2 = 1200, 760
dec = Image.new("RGB", (W2, H2), "#F5F5F0")
d = ImageDraw.Draw(dec)
box(d, (20, 16, W2 - 20, 130), "#FFFFFF", "#1a1a1a", 4)
center(d, W2 // 2, 50, "Worksheet — kapan pakai bus apa? (FS-27)", FT)
center(d, W2 // 2, 100, "OLED · BME280 · microSD — pilih bus sebelum wiring", F, "#333")

box(d, (30, 150, W2 - 30, 215), "#ECEFF1", "#1a1a1a", 3)
d.text((55, 168), "Modul", font=FH, fill="#1a1a1a")
d.text((320, 168), "Pilih bus", font=FH, fill="#1a1a1a")
d.text((560, 168), "Kenapa (bahasa manusia)", font=FH, fill="#1a1a1a")

rows = [
    ("OLED 0,96\"", "I2C", "Layar kecil + sensor lain\nbisa berbagi 2 kabel\n(SDA / SCL).", "#E3F2FD", "#1565C0"),
    ("BME280", "I2C", "Sensor tekanan/suhu:\nalamat di bus yang sama\ndengan OLED (FS-28).", "#E3F2FD", "#1565C0"),
    ("microSD", "SPI", "Kartu memori butuh\nkecepatan; SPI punya\nCS khusus per chip.", "#FFF8E1", "#F9A825"),
]
for i, (mod, bus, why, fill, out) in enumerate(rows):
    y0 = 235 + i * 145
    box(d, (30, y0, W2 - 30, y0 + 132), fill, out, 3)
    d.text((55, y0 + 50), mod, font=FH, fill="#1a1a1a")
    box(d, (300, y0 + 30, 500, y0 + 100), "#FFFFFF", out, 3)
    center(d, 400, y0 + 65, bus, FT, out)
    for j, ln in enumerate(why.split("\n")):
        d.text((560, y0 + 28 + j * 32), ln, font=F, fill="#333")

box(d, (30, 670, W2 - 30, 735), "#E8F5E9", "#2E7D32", 3)
center(
    d,
    W2 // 2,
    702,
    "UART tetap penting: Serial Monitor = jendela debug (FS-14) — bukan bus sensor banyak",
    F,
    "#1B5E20",
)
dec.save(OUT / "fs27-decision-table.png", optimize=True)
print("decision", (OUT / "fs27-decision-table.png").stat().st_size)

# --- Tools-first ---
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
W, H = 1200, 620
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 48, "I2C — banyak perangkat di 2 kabel (SDA + SCL)", FT)
center(d, W // 2, 92, "ESP32 = pengendali · tiap alat punya alamat · pin: SDA 21 · SCL 22", F, "#333")

d.line([(100, 220), (1100, 220)], fill="#1565C0", width=7)
d.line([(100, 290), (1100, 290)], fill="#2E7D32", width=7)
d.text((40, 200), "SDA", font=FH, fill="#1565C0")
d.text((40, 270), "SCL", font=FH, fill="#2E7D32")

nodes = [
    (200, "ESP32", "pengendali", "#FFECB3", "#F9A825"),
    (480, "BME280", "alamat 0x76", "#BBDEFB", "#1565C0"),
    (760, "OLED", "alamat 0x3C", "#C8E6C9", "#2E7D32"),
    (1020, "(nanti)", "perangkat lain", "#E0E0E0", "#616161"),
]
for x, title, sub, fill, out in nodes:
    box(d, (x - 100, 340, x + 100, 480), fill, out, 4)
    center(d, x, 385, title, FH, out)
    center(d, x, 435, sub, F, "#333")
    d.line([(x, 340), (x, 290)], fill="#2E7D32", width=5)
    d.line([(x, 340), (x, 220)], fill="#1565C0", width=5)

box(d, (40, 510, W - 40, 595), "#E3F2FD", "#1565C0", 3)
center(d, W // 2, 540, "2 kabel bersama + panggil alamat (bukan telepon 1 lawan 1)", F, "#0D47A1")
center(d, W // 2, 575, "Inspirasi Commons I2C.svg · Koding Indonesia (FS-27)", FS, "#0D47A1")
img.save(OUT / "fs27-i2c-labeled.png", optimize=True)
print("i2c", (OUT / "fs27-i2c-labeled.png").stat().st_size)

# --- SPI labeled (MISO arrow points device → ESP32) ---
W, H = 1200, 640
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 48, "SPI — cepat, tapi pin lebih banyak (CS per chip)", FT)
center(d, W // 2, 92, "CS = Chip Select (di buku lama sering tertulis SS) · cocok microSD", F, "#333")

box(d, (80, 145, 460, 470), "#FFF8E1", "#F9A825", 4)
center(d, 270, 185, "ESP32 (pengendali)", FH, "#F57F17")
for i, (lab, col) in enumerate(
    [("SCK  — jam", "#455A64"), ("MOSI — keluar", "#1565C0"), ("MISO — masuk", "#2E7D32"), ("CS   — pilih chip", "#C62828")]
):
    y = 230 + i * 52
    box(d, (110, y, 430, y + 42), "#FFFFFF", col, 3)
    center(d, 270, y + 21, lab, F, col)

box(d, (740, 145, 1120, 470), "#E8F5E9", "#2E7D32", 4)
center(d, 930, 185, "microSD (perangkat)", FH, "#1B5E20")
for i, (lab, col) in enumerate(
    [("SCK", "#455A64"), ("MOSI", "#1565C0"), ("MISO", "#2E7D32"), ("CS", "#C62828")]
):
    y = 230 + i * 52
    box(d, (770, y, 1090, y + 42), "#FFFFFF", col, 3)
    center(d, 930, y + 21, lab, F, col)

# SCK, MOSI, CS: ESP32 → microSD ; MISO: microSD → ESP32
for i, col in enumerate(["#455A64", "#1565C0", "#2E7D32", "#C62828"]):
    y = 251 + i * 52
    d.line([(460, y), (740, y)], fill=col, width=5)
    if i == 2:  # MISO: arrow on the left (into ESP32)
        d.polygon([(460, y), (485, y - 10), (485, y + 10)], fill=col)
    else:
        d.polygon([(740, y), (715, y - 10), (715, y + 10)], fill=col)

box(d, (40, 500, W - 40, 615), "#FFF8E1", "#F9A825", 3)
center(d, W // 2, 540, "Tiap perangkat SPI butuh CS sendiri · panah MISO balik ke ESP32", F, "#E65100")
center(d, W // 2, 580, "Inspirasi Commons SPI_single_slave.svg · Koding Indonesia (FS-27)", FS, "#E65100")
img.save(OUT / "fs27-spi-labeled.png", optimize=True)
print("spi", (OUT / "fs27-spi-labeled.png").stat().st_size)

# --- Module collage (Commons photos + labels) ---
MW, MH = 1400, 620
mod = Image.new("RGB", (MW, MH), "#F5F5F0")
d = ImageDraw.Draw(mod)
box(d, (20, 16, MW - 20, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, MW // 2, 48, "Contoh modul di jalur — kenali dulu, wiring di FS-28/FS-36", FT)
center(d, MW // 2, 92, "BME280 + OLED → I2C · microSD → SPI  (foto Commons, ber-sitasi di caption)", F, "#333")

panels = [
    (40, TMP / "oled.jpg", "OLED (contoh layar I2C)", "Bus: I2C", "#E3F2FD", "#1565C0"),
    (480, TMP / "bme280.jpg", "BME280 (sensor I2C)", "Bus: I2C", "#E3F2FD", "#1565C0"),
    (920, TMP / "microsd.jpg", "microSD (+ adapter)", "Bus: SPI", "#FFF8E1", "#F9A825"),
]
for x, src, title, bus, fill, out in panels:
    box(d, (x, 145, x + 420, 505), fill, out, 4)
    photo = fit_cover(src, (380, 240))
    mod.paste(photo, (x + 20, 160))
    center(d, x + 210, 430, title, FB, "#1a1a1a")
    center(d, x + 210, 475, bus, FH, out)

box(d, (40, 530, MW - 40, 590), "#FFFFFF", "#1a1a1a", 3)
center(
    d,
    MW // 2,
    560,
    "Ini contoh bentuk modul — bukan wiring hari ini. Sitasi lengkap di caption artikel.",
    F,
    "#333",
)
mod.save(OUT / "fs27-modul-contoh.png", optimize=True)
print("modul", (OUT / "fs27-modul-contoh.png").stat().st_size)

print("done")
