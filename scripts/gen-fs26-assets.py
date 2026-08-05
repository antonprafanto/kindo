# -*- coding: utf-8 -*-
"""Generate FS-26 cover + servo wiring schematic (Gambar utama)."""
from PIL import Image, ImageDraw, ImageFont
from pathlib import Path

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 34)
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


def ortho(d, points, fill, w=5):
    for (x1, y1), (x2, y2) in zip(points, points[1:]):
        d.line([(x1, y1), (x2, y2)], fill=fill, width=w)


# --- Cover ---
cover = Image.new("RGB", (1200, 675), "#0D47A1")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#1565C0", "#0D47A1", "#002171"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

left = Image.new("RGB", (520, 520), "#fff")
ld = ImageDraw.Draw(left)
box(ld, (8, 8, 512, 512), "#F5F5F0", "#1a1a1a", 4, 12)
try:
    kit = Image.open(OUT / "kit-servo-sg90.jpg").convert("RGB")
    kit.thumbnail((420, 360))
    left.paste(kit, ((520 - kit.width) // 2, 90))
    ld.text((40, 40), "SG90 micro servo", font=FH, fill="#0D47A1")
    ld.text((40, 470), "Signal → GPIO 13", font=FS, fill="#333")
except Exception as e:
    ld.text((40, 240), f"(servo photo) {e}", font=F, fill="#666")

cover.paste(left.resize((480, 480)), (60, 100))
center(d, 860, 200, "FS-26", FT, "#BBDEFB")
center(d, 860, 280, "Servo: gerakan", FT, "#FFFFFF")
center(d, 860, 350, "sudut dengan PWM", FH, "#FFFFFF")
center(d, 860, 430, "GPIO 13 · 0° / 90° / 180°", FS, "#FFF59D")
center(d, 860, 490, "ESP32Servo · tanpa Wi-Fi", FX, "#E3F2FD")
cover.save(OUT / "fs26-cover-servo.jpg", quality=88, optimize=True)
print("cover", (OUT / "fs26-cover-servo.jpg").stat().st_size)

# --- Wiring schematic as Gambar utama ---
W, H = 1100, 860
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)

C_5V = "#C62828"
C_SIG = "#F9A825"
C_GND = "#424242"

box(d, (20, 14, W - 20, 86), "#FFFFFF", "#1a1a1a", 3)
center(d, W // 2, 36, "Gambar utama — Servo SG90 + ESP32 (FS-26)", FT)
center(
    d,
    W // 2,
    64,
    "Signal @ GPIO 13 · VCC 5V (bukan 3V3) · GND bersama · sapu 0°→90°→180° · belum Wi-Fi",
    FS,
    "#333",
)

# ESP32
box(d, (50, 110, 360, 430), "#FFFFFF", "#2E7D32", 3)
center(d, 205, 132, "ESP32 DevKitC-1", FH, "#1B5E20")
for y, lab, fill, out in [
    (170, "5V  — daya servo", "#FFCDD2", C_5V),
    (230, "GPIO 13 / IO13  — sinyal", "#FFF59D", C_SIG),
    (290, "GND  — ground bersama", "#E0E0E0", C_GND),
    (350, "(USB 5V dari komputer)", "#E8F5E9", "#2E7D32"),
]:
    box(d, (70, y, 340, y + 42), fill, out, 2)
    center(d, 205, y + 21, lab, FS)

# Servo
box(d, (560, 130, 1040, 400), "#FFFFFF", "#1565C0", 3)
center(d, 800, 152, "Servo SG90 — “tangan kecil”", FH, "#0D47A1")
for y, lab, fill, out in [
    (190, "Merah (VCC) → 5V", "#FFCDD2", C_5V),
    (250, "Oranye/kuning (Signal) → GPIO 13", "#FFF59D", C_SIG),
    (310, "Cokelat/hitam (GND) → GND", "#E0E0E0", C_GND),
]:
    box(d, (590, y, 1010, y + 42), fill, out, 2)
    center(d, 800, y + 21, lab, FS)

# wires
ortho(d, [(360, 191), (560, 211)], C_5V, 5)
ortho(d, [(360, 251), (560, 271)], C_SIG, 5)
ortho(d, [(360, 311), (560, 331)], C_GND, 5)

# legend
box(d, (50, 450, 1040, 500), "#FFFFFF", "#1a1a1a", 2)
center(
    d,
    545,
    475,
    "Legenda: merah 5V · oranye sinyal/GPIO 13 · abu GND · warna kabel clone bisa beda — ikuti VCC / Signal / GND",
    FS,
    "#333",
)

# tip panels
box(d, (40, 520, 400, 840), "#E8F5E9", "#2E7D32", 3)
d.text((58, 540), "Cara baca modul", font=FH, fill="#1B5E20")
for i, ln in enumerate(
    [
        "1) VCC servo → 5V (jangan 3V3)",
        "2) Signal → GPIO 13 / IO13",
        "3) GND bersama ESP32",
        "4) Pasang horn/lengan kecil",
        "   agar gerakan terlihat",
        "5) Library: ESP32Servo",
        "   (Library Manager)",
        "6) Sapu sudut pelan di Serial",
    ]
):
    d.text((58, 580 + i * 26), ln, font=FX, fill="#333")

box(d, (420, 520, 720, 840), "#E3F2FD", "#1565C0", 3)
d.text((438, 540), "Servo vs LED PWM", font=FH, fill="#0D47A1")
for i, ln in enumerate(
    [
        "• LED (FS-20): terang 0–255",
        "• Servo: posisi sudut",
        "  0° / 90° / 180°",
        "• Sama-sama PWM,",
        "  arti perintah beda",
        "• write(90) = tengah",
        "  (kira-kira)",
        "• Jangan putar paksa",
        "  dengan tangan saat ON",
    ]
):
    d.text((438, 580 + i * 26), ln, font=FX, fill="#333")

box(d, (740, 520, 1060, 840), "#FFEBEE", "#C62828", 3)
d.text((758, 540), "Peringatan daya", font=FH, fill="#B71C1C")
for i, ln in enumerate(
    [
        "Servo bisa menarik",
        "arus besar saat gerak.",
        "",
        "1 SG90 ringan dari",
        "pin 5V USB biasanya OK.",
        "",
        "Kalau ESP32 reset/",
        "putus USB → pakai",
        "adaptor 5V terpisah",
        "+ GND bersama.",
    ]
):
    d.text((758, 580 + i * 22), ln, font=FX, fill="#B71C1C")

img.save(OUT / "fs26-servo-wiring.png", optimize=True)
print("wiring", (OUT / "fs26-servo-wiring.png").stat().st_size)
print("done")
