"""Regenerate FS-23 helper wiring PNG with S/+/- aliases (Pillow)."""
from PIL import Image, ImageDraw, ImageFont

W, H = 1100, 780
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)

try:
    font_title = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 28)
    font_h = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 18)
    font = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 16)
    font_s = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 14)
    font_xs = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 13)
except OSError:
    font_title = font_h = font = font_s = font_xs = ImageFont.load_default()


def box(xy, fill, outline, width=3):
    d.rounded_rectangle(xy, radius=10, fill=fill, outline=outline, width=width)


def center_text(cx, cy, text, fnt, fill="#1a1a1a"):
    bbox = d.textbbox((0, 0), text, font=fnt)
    tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
    d.text((cx - tw / 2, cy - th / 2), text, font=fnt, fill=fill)


box((20, 16, W - 20, 88), "#FFFFFF", "#1a1a1a", 3)
center_text(W // 2, 38, "Skema bantu — wiring relay aman (FS-23)", font_title)
center_text(
    W // 2,
    68,
    "ESP32 GPIO 26 → IN/S · VCC/+ ke 5V · GND/− bersama. Hari ini: klik saja — belum AC 220V.",
    font_s,
    "#333333",
)

box((60, 120, 420, 360), "#FFFFFF", "#2E7D32", 3)
center_text(240, 145, "ESP32 DevKitC-1", font_h, "#1B5E20")
pins_esp = [
    ("5V", "#FFCDD2", "#C62828"),
    ("GPIO 26", "#BBDEFB", "#1565C0"),
    ("GND", "#E0E0E0", "#424242"),
]
y = 185
for label, fill, out in pins_esp:
    box((110, y, 370, y + 40), fill, out, 2)
    center_text(240, y + 20, label, font_h)
    y += 55

box((620, 120, 1040, 400), "#FFFFFF", "#1565C0", 3)
center_text(830, 145, "Modul relay 1 channel 5V", font_h, "#0D47A1")
pins_r = [
    ("VCC  (+)", "#FFCDD2", "#C62828"),
    ("IN   (S)", "#BBDEFB", "#1565C0"),
    ("GND  (−)", "#E0E0E0", "#424242"),
]
y = 185
for label, fill, out in pins_r:
    box((690, y, 970, y + 40), fill, out, 2)
    center_text(830, y + 20, label, font_h)
    y += 55
box((690, y, 970, y + 55), "#0D47A1", "#0D47A1", 2)
center_text(830, y + 18, "RELAY", font_h, "#FFFFFF")
center_text(830, y + 40, "klik", font_s, "#BBDEFB")
center_text(830, y + 78, "Terminal sekrup beban (hari ini TIDAK dipakai AC)", font_xs, "#555555")


def wire(y1, y2, color, label):
    d.line([(420, y1), (620, y2)], fill=color, width=5)
    mid = ((420 + 620) / 2, (y1 + y2) / 2 - 14)
    d.text(mid, label, font=font_xs, fill=color)


wire(205, 205, "#C62828", "merah")
wire(260, 260, "#1565C0", "biru")
wire(315, 315, "#212121", "hitam")

box((40, 430, 380, 740), "#FFF8E1", "#F9A825", 3)
d.text((60, 448), "Kenapa 5V, bukan 3V3?", font=font_h, fill="#E65100")
for i, ln in enumerate(
    [
        "Banyak modul Songle kit butuh 5V",
        "untuk coil. Pin IN/S tetap dapat 3.3V",
        "dari GPIO 26 — biasanya cukup untuk",
        "optocoupler. GND harus bersama",
        "(satu tanah).",
    ]
):
    d.text((60, 485 + i * 28), ln, font=font_s, fill="#333333")

box((400, 430, 760, 740), "#FFFFFF", "#1a1a1a", 3)
d.text((420, 448), "Cara baca rangkaian", font=font_h, fill="#1a1a1a")
for i, ln in enumerate(
    [
        "1) VCC/+ modul → 5V ESP32",
        "   (bukan 3V3 untuk coil).",
        "2) GND/− modul → GND ESP32",
        "   (wajib bersama).",
        "3) IN/S modul → GPIO 26",
        "   (IO26 di silkscreen).",
        "4) Upload sketch — dengar klik",
        "   + lihat LED ON.",
        "5) Terminal beban: kosong OK,",
        "   atau LED DC kecil saja.",
    ]
):
    d.text((420, 485 + i * 24), ln, font=font_s, fill="#333333")

box((780, 430, 1060, 740), "#FFEBEE", "#C62828", 3)
d.text((800, 448), "Peringatan", font=font_h, fill="#B71C1C")
for i, ln in enumerate(
    [
        "JANGAN colok",
        "AC 220V / PLN",
        "di latihan hari ini.",
        "",
        "Beban DC kecil",
        "saja. Terminal",
        "NC/COM/NO boleh",
        "kosong dulu.",
    ]
):
    d.text((800, 490 + i * 28), ln, font=font, fill="#B71C1C")

out = "public/images/fsiot/fs23-relay-wiring.png"
img.save(out, optimize=True)
print("wrote", out, img.size)
