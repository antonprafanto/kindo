# -*- coding: utf-8 -*-
"""Generate FS-25 cover + PIR wiring schematic."""
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
cover = Image.new("RGB", (1200, 675), "#1B5E20")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#2E7D32", "#1B5E20", "#0D3B12"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

left = Image.new("RGB", (520, 520), "#fff")
ld = ImageDraw.Draw(left)
box(ld, (8, 8, 512, 512), "#F5F5F0", "#1a1a1a", 4, 12)
try:
    pir = Image.open(OUT / "kit-pir-hcsr501.jpg").convert("RGB")
    pir.thumbnail((380, 380))
    left.paste(pir, ((520 - pir.width) // 2, 70))
    ld.text((40, 40), "HC-SR501 PIR", font=FH, fill="#2E7D32")
    ld.text((40, 460), "OUT → GPIO 25", font=FS, fill="#333")
except Exception as e:
    ld.text((40, 240), f"(PIR photo) {e}", font=F, fill="#666")

cover.paste(left.resize((480, 480)), (60, 100))
center(d, 860, 200, "FS-25", FT, "#C8E6C9")
center(d, 860, 280, "PIR: ada gerak", FT, "#FFFFFF")
center(d, 860, 350, "atau tidak?", FH, "#FFFFFF")
center(d, 860, 430, "GPIO 25 · LED GPIO 2", FS, "#FFF59D")
center(d, 860, 490, "settle dulu · tanpa Wi-Fi", FX, "#E8F5E9")
cover.save(OUT / "fs25-cover-pir.jpg", quality=88, optimize=True)
print("cover", (OUT / "fs25-cover-pir.jpg").stat().st_size)

# --- Wiring schematic 1100x820 ---
W, H = 1100, 820
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)

C_5V = "#C62828"
C_OUT = "#1565C0"
C_GND = "#424242"
C_LED = "#F9A825"

box(d, (20, 14, W - 20, 86), "#FFFFFF", "#1a1a1a", 3)
center(d, W // 2, 36, "Gambar utama — PIR HC-SR501 + LED (FS-25)", FT)
center(
    d,
    W // 2,
    64,
    "PIR OUT @ GPIO 25 (5V) · LED @ GPIO 2 · settle ~30–60 dtk setelah power · belum Wi-Fi",
    FS,
    "#333",
)

# ESP32
box(d, (50, 110, 340, 430), "#FFFFFF", "#2E7D32", 3)
center(d, 195, 132, "ESP32 DevKitC-1", FH, "#1B5E20")
for y, lab, fill, out in [
    (170, "5V", "#FFCDD2", C_5V),
    (230, "GPIO 25", "#BBDEFB", C_OUT),
    (290, "GPIO 2", "#FFF59D", C_LED),
    (350, "GND", "#E0E0E0", C_GND),
]:
    box(d, (80, y, 310, y + 40), fill, out, 2)
    center(d, 195, y + 20, lab, FH)

# PIR
box(d, (560, 110, 1040, 310), "#FFFFFF", "#2E7D32", 3)
center(d, 800, 132, "PIR HC-SR501 — indra gerak", FH, "#1B5E20")
for y, lab, fill, out in [
    (165, "VCC → 5V", "#FFCDD2", C_5V),
    (215, "OUT → GPIO 25", "#BBDEFB", C_OUT),
    (265, "GND → GND", "#E0E0E0", C_GND),
]:
    box(d, (600, y, 1000, y + 38), fill, out, 2)
    center(d, 800, y + 19, lab, FS)

# LED
box(d, (560, 340, 1040, 470), "#FFFFFF", "#F9A825", 3)
center(d, 800, 362, "LED (GPIO 2) — “lampu meja” mini", FH, "#F57F17")
for y, lab, fill, out in [
    (390, "Anoda (+ resistor) → GPIO 2", "#FFF59D", C_LED),
    (430, "Katoda → GND", "#E0E0E0", C_GND),
]:
    box(d, (600, y, 1000, y + 34), fill, out, 2)
    center(d, 800, y + 17, lab, FS)

# wires no cross: PIR above LED
ortho(d, [(310, 190), (560, 184)], C_5V, 5)
ortho(d, [(310, 250), (560, 234)], C_OUT, 5)
ortho(d, [(310, 370), (420, 370), (420, 284), (560, 284)], C_GND, 5)
ortho(d, [(310, 310), (480, 310), (480, 407), (560, 407)], C_LED, 5)
ortho(d, [(420, 370), (420, 447), (560, 447)], C_GND, 5)

box(d, (50, 490, 1040, 530), "#FFFFFF", "#1a1a1a", 2)
center(
    d,
    545,
    510,
    "Legenda: merah 5V · biru OUT/GPIO 25 · kuning LED/GPIO 2 · abu GND · jumper H (ulang) disarankan",
    FS,
    "#333",
)

box(d, (40, 545, 400, 800), "#E8F5E9", "#2E7D32", 3)
d.text((58, 560), "Cara baca modul", font=FH, fill="#1B5E20")
for i, ln in enumerate(
    [
        "1) VCC → 5V (bukan 3V3)",
        "2) OUT → GPIO 25 / IO25",
        "3) GND bersama ESP32",
        "4) LED di GPIO 2 (opsional",
        "   tapi memudahkan uji)",
        "5) Setelah colok USB: tunggu",
        "   30–60 dtk (settle)",
        "6) Gerakkan tangan di depan",
        "   lensa putih Fresnel",
    ]
):
    d.text((58, 598 + i * 20), ln, font=FX, fill="#333")

box(d, (420, 545, 720, 800), "#FFF8E1", "#F9A825", 3)
d.text((438, 560), "Potensio & jumper", font=FH, fill="#E65100")
for i, ln in enumerate(
    [
        "• Sensitivity: jarak deteksi",
        "  (putar pelan)",
        "• Time delay: lama OUT HIGH",
        "  setelah gerak",
        "• Jumper H = ulangi trigger",
        "  (lebihkan awam)",
        "• Jumper L = sekali lalu diam",
        "  sampai delay habis",
        "Jangan putar ekstrem dulu.",
    ]
):
    d.text((438, 598 + i * 20), ln, font=FX, fill="#333")

box(d, (740, 545, 1060, 800), "#FFEBEE", "#C62828", 3)
d.text((758, 560), "Peringatan", font=FH, fill="#B71C1C")
for i, ln in enumerate(
    [
        "False trigger wajar",
        "saat baru nyala.",
        "",
        "Jangan uji terlalu dekat",
        "di 10 detik pertama.",
        "",
        "Tanpa Wi-Fi / MQTT.",
        "Relay opsional nanti",
        "(GPIO 26 seperti FS-23).",
    ]
):
    d.text((758, 598 + i * 20), ln, font=F, fill="#B71C1C")

img.save(OUT / "fs25-pir-wiring.png", optimize=True)
print("wiring", (OUT / "fs25-pir-wiring.png").stat().st_size)
print("done")
