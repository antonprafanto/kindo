# -*- coding: utf-8 -*-
"""Generate FS-24 cover + combined wiring diagram."""
from PIL import Image, ImageDraw, ImageFont
from pathlib import Path

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 36)
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


# --- Cover 1200x675 ---
cover = Image.new("RGB", (1200, 675), "#0D47A1")
d = ImageDraw.Draw(cover)
# gradient-ish bands
for i, c in enumerate(["#1565C0", "#0D47A1", "#01579B"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

# paste kit photos if available
left = Image.new("RGB", (520, 520), "#fff")
ld = ImageDraw.Draw(left)
box(ld, (8, 8, 512, 512), "#F5F5F0", "#1a1a1a", 4, 12)
try:
    dht = Image.open(OUT / "kit-dht22.jpg").convert("RGB")
    rel = Image.open(OUT / "kit-relay-5v.jpg").convert("RGB")
    dht.thumbnail((230, 230))
    rel.thumbnail((230, 230))
    left.paste(dht, (40, 140))
    left.paste(rel, (260, 140))
    ld.text((40, 40), "DHT22", font=FH, fill="#E65100")
    ld.text((260, 40), "Relay 5V", font=FH, fill="#1565C0")
    ld.text((40, 400), "GPIO 4", font=FS, fill="#333")
    ld.text((260, 400), "GPIO 26", font=FS, fill="#333")
except Exception as e:
    ld.text((40, 240), f"(kit photos) {e}", font=F, fill="#666")

cover.paste(left.resize((480, 480)), (60, 100))
center(d, 860, 200, "FS-24", FT, "#BBDEFB")
center(d, 860, 280, "Otomasi lokal", FT, "#FFFFFF")
center(d, 860, 350, "panas → “kipas” (relay)", FH, "#FFFFFF")
center(d, 860, 430, "DHT22 + GPIO 26 · histeresis", FS, "#FFF59D")
center(d, 860, 490, "belum AC 220V · tanpa Wi-Fi", FX, "#E3F2FD")
cover.save(OUT / "fs24-cover-otomasi.jpg", quality=88, optimize=True)
print("cover", (OUT / "fs24-cover-otomasi.jpg").stat().st_size)

# --- Wiring main 1100x820 ---
W, H = 1100, 820
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 88), "#FFFFFF", "#1a1a1a", 3)
center(d, W // 2, 38, "Gambar utama — otomasi lokal panas → relay (FS-24)", FT)
center(d, W // 2, 68, "DHT22 @ GPIO 4 (3V3) + relay @ GPIO 26 (5V). Hari ini: klik/“kipas” DC — belum AC 220V.", FS, "#333")

# ESP32 center-left
box(d, (60, 120, 380, 420), "#FFFFFF", "#2E7D32", 3)
center(d, 220, 145, "ESP32 DevKitC-1", FH, "#1B5E20")
for y, (lab, fill, out) in zip(
    [185, 245, 305, 365],
    [
        ("3V3", "#FFECB3", "#F9A825"),
        ("GPIO 4", "#FFE0B2", "#EF6C00"),
        ("GPIO 26", "#BBDEFB", "#1565C0"),
        ("GND", "#E0E0E0", "#424242"),
    ],
):
    box(d, (100, y, 340, y + 42), fill, out, 2)
    center(d, 220, y + 21, lab, FH)

# DHT box
box(d, (460, 120, 720, 320), "#FFFFFF", "#EF6C00", 3)
center(d, 590, 145, "DHT22 (modul kit)", FH, "#E65100")
for y, (lab, fill, out) in zip(
    [185, 235, 285],
    [
        ("VCC → 3V3", "#FFECB3", "#F9A825"),
        ("DATA → GPIO 4", "#FFE0B2", "#EF6C00"),
        ("GND → GND", "#E0E0E0", "#424242"),
    ],
):
    box(d, (490, y, 690, y + 38), fill, out, 2)
    center(d, 590, y + 19, lab, FS)

# Relay box
box(d, (780, 120, 1040, 360), "#FFFFFF", "#1565C0", 3)
center(d, 910, 145, "Relay 5V (S / + / −)", FH, "#0D47A1")
for y, (lab, fill, out) in zip(
    [185, 235, 285],
    [
        ("+ / VCC → 5V", "#FFCDD2", "#C62828"),
        ("S / IN → GPIO 26", "#BBDEFB", "#1565C0"),
        ("− / GND → GND", "#E0E0E0", "#424242"),
    ],
):
    box(d, (810, y, 1010, y + 38), fill, out, 2)
    center(d, 910, y + 19, lab, FS)
box(d, (810, 335, 1010, 365), "#0D47A1", "#0D47A1", 2)
center(d, 910, 350, "klik / “kipas” DC", FS, "#fff")

# wires (simple)
d.line([(380, 205), (460, 205)], fill="#F9A825", width=4)
d.line([(380, 265), (460, 255)], fill="#EF6C00", width=4)
d.line([(380, 385), (460, 305)], fill="#424242", width=4)
d.line([(380, 325), (780, 255)], fill="#1565C0", width=4)
# note 5V from ESP - show small callout
box(d, (60, 440, 380, 500), "#FFCDD2", "#C62828", 2)
center(d, 220, 470, "Juga: pin 5V ESP32 → + relay (coil)", FS, "#B71C1C")
d.line([(380, 470), (780, 205)], fill="#C62828", width=4)

# bottom info
box(d, (40, 520, 420, 780), "#FFF8E1", "#F9A825", 3)
d.text((60, 540), "Ambang + histeresis", font=FH, fill="#E65100")
for i, ln in enumerate(
    [
        "Contoh latihan:",
        "• ON  jika T ≥ 30 °C",
        "• OFF jika T ≤ 28 °C",
        "Jarak 2 °C = histeresis —",
        "hindari on/off berganti-",
        "ganti di batas yang sama.",
        "Ubah angka di sketch jika",
        "ruanganmu lebih panas/dingin.",
    ]
):
    d.text((60, 580 + i * 22), ln, font=FS, fill="#333")

box(d, (440, 520, 760, 780), "#FFFFFF", "#1a1a1a", 3)
d.text((460, 540), "Urutan baca rangkaian", font=FH, fill="#1a1a1a")
for i, ln in enumerate(
    [
        "1) DHT: 3V3 · GPIO 4 · GND",
        "   (seperti FS-21).",
        "2) Relay: 5V · GPIO 26 · GND",
        "   (seperti FS-23).",
        "3) GND bersama wajib.",
        "4) Terminal beban boleh kosong",
        "   — fokus klik + Serial.",
        "5) Library DHT sudah terpasang",
        "   (Library Manager).",
    ]
):
    d.text((460, 580 + i * 20), ln, font=FX, fill="#333")

box(d, (780, 520, 1060, 780), "#FFEBEE", "#C62828", 3)
d.text((800, 540), "Peringatan", font=FH, fill="#B71C1C")
for i, ln in enumerate(
    [
        "JANGAN colok",
        "AC 220V / PLN.",
        "",
        "“Kipas” = metafora:",
        "klik relay / LED DC",
        "kecil sudah cukup.",
        "",
        "Tanpa Wi-Fi/MQTT.",
    ]
):
    d.text((800, 580 + i * 22), ln, font=F, fill="#B71C1C")

img.save(OUT / "fs24-otomasi-wiring.png", optimize=True)
print("wiring", (OUT / "fs24-otomasi-wiring.png").stat().st_size)
print("done")
