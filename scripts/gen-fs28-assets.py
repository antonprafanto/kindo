# -*- coding: utf-8 -*-
"""Generate FS-28 / #98 cover + diagrams (I2C BME280 + OLED)."""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)
TMP = Path("tmp-qa98")
TMP.mkdir(parents=True, exist_ok=True)

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 44)
    FH = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 32)
    FB = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 28)
    F = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 26)
    FS = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 22)
except OSError:
    FT = FH = FB = F = FS = ImageFont.load_default()


def center(d, cx, cy, text, font, fill="#1a1a1a"):
    ox = -1.0 if len(text) == 1 and text.isdigit() else 0
    d.text((cx + ox, cy - 1), text, font=font, fill=fill, anchor="mm")


def box(d, xy, fill, outline, w=4, r=14):
    d.rounded_rectangle(xy, radius=r, fill=fill, outline=outline, width=w)


def fit_cover(src: Path, size=(340, 240)) -> Image.Image:
    im = Image.open(src).convert("RGB")
    im.thumbnail(size, Image.Resampling.LANCZOS)
    canvas = Image.new("RGB", size, "#FFFFFF")
    canvas.paste(im, ((size[0] - im.width) // 2, (size[1] - im.height) // 2))
    return canvas


def draw_oled(size=(360, 220)) -> Image.Image:
    w, h = size
    canvas = Image.new("RGB", size, "#FFFFFF")
    d = ImageDraw.Draw(canvas)
    box(d, (24, 18, w - 24, h - 28), "#1565C0", "#0D47A1", 3, 10)
    box(d, (48, 36, w - 48, 130), "#111111", "#000000", 2, 6)
    center(d, w // 2, 78, "24.6 C", FH, "#90CAF9")
    center(d, w // 2, 112, "1013 hPa", FS, "#BBDEFB")
    # Match common I2C OLED silkscreen: GND · VDD/VCC · SCK(=SCL) · SDA
    pins = [("GND", "#333"), ("VDD", "#C62828"), ("SCK", "#2E7D32"), ("SDA", "#1565C0")]
    pw = 52
    x0 = (w - len(pins) * pw) // 2
    for i, (lab, col) in enumerate(pins):
        x = x0 + i * pw
        box(d, (x + 4, h - 78, x + pw - 4, h - 44), "#FFFDE7", col, 2, 6)
        center(d, x + pw // 2, h - 61, lab, FS, col)
    center(d, w // 2, h - 22, "SCK = SCL · VDD = VCC · alamat 0x3C", FS, "#0D47A1")
    return canvas


# --- Cover ---
cover = Image.new("RGB", (1200, 675), "#0D47A1")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#1565C0", "#0D47A1", "#002171"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

box(d, (70, 70, 530, 330), "#E3F2FD", "#FFFFFF", 4, 16)
center(d, 300, 140, "BME280", FT, "#0D47A1")
center(d, 300, 210, "sensor I2C", FH, "#1a1a1a")
center(d, 300, 270, "alamat 0x76", FB, "#333")

box(d, (670, 70, 1130, 330), "#E8F5E9", "#FFFFFF", 4, 16)
center(d, 900, 140, "OLED", FT, "#1B5E20")
center(d, 900, 210, "layar I2C", FH, "#1a1a1a")
center(d, 900, 270, "alamat 0x3C", FB, "#333")

center(d, 600, 400, "FS-28", FT, "#BBDEFB")
center(d, 600, 470, "Praktik I2C: BME280 + OLED", FT, "#FFFFFF")
center(d, 600, 545, "SDA 21 · SCL 22 · angka di layar = Serial", FB, "#E3F2FD")
center(d, 600, 610, "Arduino IDE · Library Manager · Upload", FH, "#90CAF9")
cover.save(OUT / "fs28-cover-i2c.jpg", quality=88, optimize=True)
cover.save(OUT / "fs28-cover-i2c.webp", "WEBP", quality=85)
print("cover", (OUT / "fs28-cover-i2c.jpg").stat().st_size)

# --- Library Manager steps (3 libs) ---
W, H = 1200, 560
lib = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(lib)
box(d, (20, 16, W - 20, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 48, "Library Manager — pasang 3 library dulu", FT)
center(d, W // 2, 92, "Baru setelah ini Verify / Upload sketch FS28_bme280_oled", F, "#333")

steps = [
    ("1", "Adafruit GFX\nLibrary", "Fondasi gambar\nuntuk OLED", "#E3F2FD", "#1565C0"),
    ("2", "Adafruit\nSSD1306", "Driver layar\n0,96\" I2C", "#E8F5E9", "#2E7D32"),
    ("3", "Adafruit\nBME280", "Sensor suhu &\ntekanan I2C", "#FFF8E1", "#F9A825"),
]
for i, (num, title, body, fill, out) in enumerate(steps):
    x0 = 40 + i * 390
    box(d, (x0, 150, x0 + 360, 480), fill, out, 4)
    box(d, (x0 + 24, 175, x0 + 96, 247), "#FFFFFF", out, 3)
    center(d, x0 + 60, 211, num, FT, out)
    for j, ln in enumerate(title.split("\n")):
        d.text((x0 + 118, 185 + j * 38), ln, font=FH, fill="#1a1a1a")
    for j, ln in enumerate(body.split("\n")):
        d.text((x0 + 36, 320 + j * 36), ln, font=F, fill="#333")
    if i < 2:
        center(d, x0 + 375, 315, "→", FT, "#1a1a1a")
lib.save(OUT / "fs28-library-manager.png", optimize=True)
print("lib", (OUT / "fs28-library-manager.png").stat().st_size)

# --- Tools / desk order ---
tl = Image.new("RGB", (1400, 720), "#F5F5F0")
d = ImageDraw.Draw(tl)
box(d, (20, 16, 1380, 130), "#FFFFFF", "#1a1a1a", 4)
center(d, 700, 50, "Urutan tools hari ini — IDE dulu, bukan Laragon", FT)
center(d, 700, 100, "Wiring → Library Manager → Verify → Upload → Serial 115200 → lihat OLED", FB, "#333")
order = [
    ("1", "Rakit\nwiring", "SDA 21 · SCL 22\n+ VCC · GND", "#E8F5E9", "#2E7D32"),
    ("2", "Pasang\nlibrary", "GFX · SSD1306\n· BME280", "#E3F2FD", "#1565C0"),
    ("3", "Upload\nsketch", "FS28_bme280_oled\n+ Serial Monitor", "#FFF8E1", "#F9A825"),
    ("4", "Centang\nchecklist", "10/10 di\nbrowser artikel", "#FCE4EC", "#C62828"),
]
for i, (num, title, body, fill, out) in enumerate(order):
    x0 = 40 + i * 340
    box(d, (x0, 170, x0 + 310, 520), fill, out, 4)
    box(d, (x0 + 20, 195, x0 + 90, 265), "#FFFFFF", out, 3)
    center(d, x0 + 55, 230, num, FT, out)
    for j, ln in enumerate(title.split("\n")):
        d.text((x0 + 108, 205 + j * 40), ln, font=FH, fill="#1a1a1a")
    for j, ln in enumerate(body.split("\n")):
        d.text((x0 + 28, 340 + j * 40), ln, font=F, fill="#333")
box(d, (40, 555, 1360, 690), "#FFFFFF", "#1a1a1a", 3)
center(d, 700, 600, "Tidak perlu hari ini:", FH, "#1a1a1a")
center(d, 700, 655, "Laragon  ·  php artisan  ·  Wi-Fi  ·  MQTT  ·  SPI / microSD", FB, "#333")
tl.save(OUT / "fs28-tools-ide.png", optimize=True)
print("tools", (OUT / "fs28-tools-ide.png").stat().st_size)

# --- Main wiring schema (labeled) ---
W, H = 1200, 720
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 48, "Skema bantu — wiring I2C bersama (FS-28)", FT)
center(d, W // 2, 92, "Satu bus: SDA + SCL · dua perangkat · pin jalur: 21 / 22", F, "#333")

# ESP32 box
box(d, (40, 160, 380, 520), "#FFF8E1", "#F9A825", 4)
center(d, 210, 200, "ESP32", FT, "#F57F17")
center(d, 210, 250, "pengendali", FH, "#1a1a1a")
for i, (lab, col) in enumerate(
    [("3V3 → VCC modul", "#C62828"), ("GND bersama", "#333"), ("GPIO 21 = SDA", "#1565C0"), ("GPIO 22 = SCL", "#2E7D32")]
):
    y = 300 + i * 48
    box(d, (70, y, 350, y + 40), "#FFFFFF", col, 3)
    center(d, 210, y + 20, lab, F, col)

# Bus lines
d.line([(380, 360), (820, 360)], fill="#1565C0", width=7)
d.line([(380, 420), (820, 420)], fill="#2E7D32", width=7)
center(d, 600, 340, "SDA", FH, "#1565C0")
center(d, 600, 455, "SCL", FH, "#2E7D32")

# Devices
box(d, (820, 170, 1160, 330), "#BBDEFB", "#1565C0", 4)
center(d, 990, 215, "BME280", FT, "#0D47A1")
center(d, 990, 265, "alamat 0x76", FH, "#1a1a1a")
center(d, 990, 305, "(kadang 0x77)", FS, "#333")

box(d, (820, 370, 1160, 530), "#C8E6C9", "#2E7D32", 4)
center(d, 990, 415, "OLED 0,96\"", FT, "#1B5E20")
center(d, 990, 465, "alamat 0x3C", FH, "#1a1a1a")
center(d, 990, 505, "SSD1306 I2C", FS, "#333")

box(d, (40, 555, W - 40, 690), "#E3F2FD", "#1565C0", 3)
center(d, W // 2, 590, "SDA→SDA · SCL/SCK→SCL · VCC/VDD→3V3 saja (jangan campur pin 5V ke rail yang sama)", F, "#0D47A1")
center(d, W // 2, 645, "Sumber: diagram Koding Indonesia (FS-28) · pin tabel FS-17", FS, "#0D47A1")
img.save(OUT / "fs28-i2c-wiring.png", optimize=True)
print("wiring", (OUT / "fs28-i2c-wiring.png").stat().st_size)

# --- Module collage ---
MW, MH = 1400, 620
mod = Image.new("RGB", (MW, MH), "#F5F5F0")
d = ImageDraw.Draw(mod)
box(d, (20, 16, MW - 20, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, MW // 2, 48, "Modul hari ini — kenali sebelum colok", FT)
center(d, MW // 2, 92, "BME280 (foto Commons) · OLED (ilustrasi tipikal) · bus I2C bersama", F, "#333")

bme_src = Path("tmp-qa97/bme280.jpg")
if not bme_src.is_file():
    bme_src = OUT / "kit-i2c-bus.png"

box(d, (40, 145, 460, 505), "#E3F2FD", "#1565C0", 4)
mod.paste(fit_cover(bme_src, (380, 240)), (60, 165))
center(d, 250, 440, "BME280 (sensor I2C)", FB, "#1a1a1a")
center(d, 250, 480, "Bus: I2C · 0x76/0x77", FH, "#1565C0")

box(d, (490, 145, 910, 505), "#E8F5E9", "#2E7D32", 4)
mod.paste(draw_oled((380, 240)), (510, 165))
center(d, 700, 440, "OLED 0,96\" (bentuk tipikal)", FB, "#1a1a1a")
center(d, 700, 480, "Bus: I2C · 0x3C", FH, "#2E7D32")

box(d, (940, 145, 1360, 505), "#FFF8E1", "#F9A825", 4)
center(d, 1150, 230, "Pin bersama", FT, "#F57F17")
for j, ln in enumerate(["SDA → GPIO 21", "SCL/SCK → GPIO 22", "VDD/VCC → 3V3", "GND → GND"]):
    center(d, 1150, 310 + j * 42, ln, FH, "#1a1a1a")

box(d, (40, 530, MW - 40, 590), "#FFFFFF", "#1a1a1a", 3)
center(d, MW // 2, 560, "Sitasi foto BME280 di caption artikel · OLED = ilustrasi Koding Indonesia", F, "#333")
mod.save(OUT / "fs28-modul-kit.png", optimize=True)
print("modul", (OUT / "fs28-modul-kit.png").stat().st_size)

# --- Success panel: Serial + OLED ---
ok = Image.new("RGB", (1200, 520), "#F5F5F0")
d = ImageDraw.Draw(ok)
box(d, (20, 16, 1180, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 58, "Sukses = angka OLED selaras Serial Monitor", FT)

box(d, (40, 130, 580, 470), "#263238", "#1a1a1a", 4)
center(d, 310, 170, "Serial Monitor · 115200", FH, "#80CBC4")
lines = [
    "FS28_bme280_oled ready",
    "BME OK @ 0x76",
    "OLED OK @ 0x3C",
    "T=24.6 C  P=1013.2 hPa",
    "T=24.7 C  P=1013.1 hPa",
]
for i, ln in enumerate(lines):
    d.text((70, 220 + i * 40), ln, font=F, fill="#FFF59D" if i >= 3 else "#80CBC4")

box(d, (620, 130, 1160, 470), "#111111", "#1a1a1a", 4)
center(d, 890, 200, "OLED", FH, "#90CAF9")
center(d, 890, 280, "24.6 C", FT, "#FFFFFF")
center(d, 890, 350, "1013 hPa", FH, "#BBDEFB")
center(d, 890, 420, "I2C OK", FB, "#81C784")
ok.save(OUT / "fs28-success-oled-serial.png", optimize=True)
print("success", (OUT / "fs28-success-oled-serial.png").stat().st_size)

print("done")
