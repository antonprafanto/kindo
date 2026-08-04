# -*- coding: utf-8 -*-
"""Generate FS-24 cover + tidy combined wiring schematic (no crossed wires)."""
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


def hline(d, x1, x2, y, fill, w=5):
    d.line([(x1, y), (x2, y)], fill=fill, width=w)


def vline(d, x, y1, y2, fill, w=5):
    d.line([(x, y1), (x, y2)], fill=fill, width=w)


def ortho(d, points, fill, w=5):
    """Polyline through axis-aligned points."""
    for (x1, y1), (x2, y2) in zip(points, points[1:]):
        d.line([(x1, y1), (x2, y2)], fill=fill, width=w)


# --- Cover 1200x675 ---
cover = Image.new("RGB", (1200, 675), "#0D47A1")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#1565C0", "#0D47A1", "#01579B"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

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

# --- Wiring tidy 1100x860 ---
# Layout: ESP left | DHT top-right | Relay bottom-right
# Pin rows on ESP aligned so DHT wires stay above relay wires (no cross).
W, H = 1100, 860
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)

box(d, (20, 14, W - 20, 86), "#FFFFFF", "#1a1a1a", 3)
center(d, W // 2, 36, "Skema bantu — otomasi lokal panas → relay (FS-24)", FT)
center(
    d,
    W // 2,
    64,
    "Warna = gambar utama · kuning GPIO 4 · oranye 3V3 · biru GPIO 26 · merah 5V · abu GND",
    FS,
    "#333",
)

# Colors
C_3V3 = "#EF6C00"
C_GPIO4 = "#F9A825"
C_GPIO26 = "#1565C0"
C_5V = "#C62828"
C_GND = "#424242"

# ESP32 left
esp = (50, 110, 340, 470)
box(d, esp, "#FFFFFF", "#2E7D32", 3)
center(d, 195, 132, "ESP32 DevKitC-1", FH, "#1B5E20")

esp_pins = [
    (170, "3V3", "#FFE0B2", C_3V3),
    (230, "GPIO 4", "#FFF59D", C_GPIO4),
    (290, "GPIO 26", "#BBDEFB", C_GPIO26),
    (350, "GND", "#E0E0E0", C_GND),
    (410, "5V", "#FFCDD2", C_5V),
]
for y, lab, fill, out in esp_pins:
    box(d, (80, y, 310, y + 40), fill, out, 2)
    center(d, 195, y + 20, lab, FH)

# DHT top-right (aligned to 3V3 / GPIO4 / GND)
dht = (560, 110, 1040, 280)
box(d, dht, "#FFFFFF", "#EF6C00", 3)
center(d, 800, 132, "DHT22 (modul kit) — indra", FH, "#E65100")
dht_pins = [
    (165, "VCC → 3V3", "#FFE0B2", C_3V3),
    (210, "DATA → GPIO 4", "#FFF59D", C_GPIO4),
    (255, "GND → GND", "#E0E0E0", C_GND),
]
for y, lab, fill, out in dht_pins:
    box(d, (600, y, 1000, y + 36), fill, out, 2)
    center(d, 800, y + 18, lab, FS)

# Relay bottom-right (aligned to GPIO26 / 5V / GND)
rel = (560, 320, 1040, 500)
box(d, rel, "#FFFFFF", "#1565C0", 3)
center(d, 800, 342, "Relay 5V (S / + / −) — otot", FH, "#0D47A1")
rel_pins = [
    (370, "S / IN → GPIO 26", "#BBDEFB", C_GPIO26),
    (415, "+ / VCC → 5V", "#FFCDD2", C_5V),
    (460, "− / GND → GND", "#E0E0E0", C_GND),
]
for y, lab, fill, out in rel_pins:
    box(d, (600, y, 1000, y + 36), fill, out, 2)
    center(d, 800, y + 18, lab, FS)
box(d, (600, 505, 1000, 535), "#0D47A1", "#0D47A1", 2)
center(d, 800, 520, "klik / “kipas” DC — belum AC 220V", FS, "#fff")

# Orthogonal wires — no crossings
# 3V3: ESP mid-right → DHT VCC
ortho(d, [(310, 190), (560, 190)], C_3V3, 5)
# GPIO4
ortho(d, [(310, 250), (560, 228)], C_GPIO4, 5)
# GPIO26 — go down a bit then right to relay S (below DHT band)
ortho(d, [(310, 310), (420, 310), (420, 388), (560, 388)], C_GPIO26, 5)
# 5V
ortho(d, [(310, 430), (480, 430), (480, 433), (560, 433)], C_5V, 5)
# GND shared bus under both: ESP → junction → up to DHT GND and right to relay GND
ortho(d, [(310, 370), (500, 370), (500, 273), (560, 273)], C_GND, 5)  # to DHT
ortho(d, [(500, 370), (500, 478), (560, 478)], C_GND, 5)  # to relay

# Tiny legend strip
box(d, (50, 545, 1040, 575), "#FFFFFF", "#1a1a1a", 2)
center(
    d,
    545,
    560,
    "Legenda: oranye 3V3 · kuning GPIO 4 · biru GPIO 26 · merah 5V · abu GND bersama",
    FS,
    "#333",
)

# Bottom info cards
box(d, (40, 590, 400, 840), "#FFF8E1", "#F9A825", 3)
d.text((58, 608), "Ambang + histeresis", font=FH, fill="#E65100")
for i, ln in enumerate(
    [
        "Contoh latihan:",
        "• ON  jika T ≥ 30 °C",
        "• OFF jika T ≤ 28 °C",
        "Jarak 2 °C = histeresis —",
        "hindari klik bolak-balik",
        "di sekitar satu angka.",
        "Ubah angka di sketch jika",
        "ruanganmu beda suhu.",
    ]
):
    d.text((58, 645 + i * 22), ln, font=FS, fill="#333")

box(d, (420, 590, 720, 840), "#FFFFFF", "#1a1a1a", 3)
d.text((438, 608), "Urutan baca", font=FH, fill="#1a1a1a")
for i, ln in enumerate(
    [
        "1) DHT: 3V3 · GPIO 4 · GND",
        "2) Relay: 5V · GPIO 26 · GND",
        "3) GND bersama wajib",
        "4) Jangan satukan 3V3+5V",
        "   di satu rail kontinu",
        "5) Terminal beban boleh kosong",
        "6) Library DHT di Library",
        "   Manager sebelum Verify",
    ]
):
    d.text((438, 645 + i * 22), ln, font=FX, fill="#333")

box(d, (740, 590, 1060, 840), "#FFEBEE", "#C62828", 3)
d.text((758, 608), "Peringatan", font=FH, fill="#B71C1C")
for i, ln in enumerate(
    [
        "JANGAN colok",
        "AC 220V / PLN.",
        "",
        "“Kipas” = metafora:",
        "klik relay / LED DC",
        "sudah cukup.",
        "",
        "Tanpa Wi-Fi / MQTT.",
    ]
):
    d.text((758, 645 + i * 22), ln, font=F, fill="#B71C1C")

img.save(OUT / "fs24-otomasi-wiring.png", optimize=True)
print("wiring", (OUT / "fs24-otomasi-wiring.png").stat().st_size)
print("done")
