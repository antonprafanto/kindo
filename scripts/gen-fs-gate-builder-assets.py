# -*- coding: utf-8 -*-
"""Generate Gate BUILDER → CONNECTED cover + diagrams."""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 40)
    FH = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 28)
    FB = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 24)
    F = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 22)
    FS = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 18)
except OSError:
    FT = FH = FB = F = FS = ImageFont.load_default()


def center(d, cx, cy, text, font, fill="#1a1a1a"):
    d.text((cx, cy), text, font=font, fill=fill, anchor="mm")


def box(d, xy, fill, outline, w=4, r=14):
    d.rounded_rectangle(xy, radius=r, fill=fill, outline=outline, width=w)


# --- Cover ---
cover = Image.new("RGB", (1200, 675), "#1B5E20")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#43A047", "#2E7D32", "#1B5E20"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

box(d, (60, 60, 420, 300), "#E8F5E9", "#FFFFFF", 4, 16)
center(d, 240, 120, "BUILDER", FT, "#1B5E20")
center(d, 240, 185, "sensor lokal", FH, "#1a1a1a")
center(d, 240, 245, "tanpa internet", FB, "#333")

box(d, (450, 100, 750, 260), "#FFF8E1", "#FFFFFF", 4, 16)
center(d, 600, 150, "Gate", FT, "#F57F17")
center(d, 600, 210, "kuis ≥12/15", FH, "#1a1a1a")

box(d, (780, 60, 1140, 300), "#E3F2FD", "#FFFFFF", 4, 16)
center(d, 960, 120, "CONNECTED", FT, "#0D47A1")
center(d, 960, 185, "Wi-Fi · HTTP", FH, "#1a1a1a")
center(d, 960, 245, "MQTT …", FB, "#333")

center(d, 600, 400, "Gate BUILDER", FT, "#C8E6C9")
center(d, 600, 465, "Pintu naik ke CONNECTED", FT, "#FFFFFF")
center(d, 600, 530, "Kuis matching di browser · target 12/15", FH, "#E8F5E9")
center(d, 600, 590, "Tanpa Laragon · tanpa Upload sketch hari ini", FS, "#A5D6A7")
cover.save(OUT / "fs-gate-builder-cover.jpg", quality=88, optimize=True)
cover.save(OUT / "fs-gate-builder-cover.webp", "WEBP", quality=85)
print("cover", (OUT / "fs-gate-builder-cover.jpg").stat().st_size)

# --- Tools ---
tl = Image.new("RGB", (1400, 640), "#F5F5F0")
d = ImageDraw.Draw(tl)
box(d, (20, 16, 1380, 110), "#FFFFFF", "#1a1a1a", 4)
center(d, 700, 42, "Urutan tools hari ini — browser saja", FT)
center(d, 700, 82, "Baca kriteria → kuis matching → Cek skor → checklist", FB, "#333")
steps = [
    ("1", "Buka\nbrowser", "Artikel Gate\nBUILDER", "#E8F5E9", "#2E7D32"),
    ("2", "Kerjakan\nkuis", "15 istilah\nmatching", "#E3F2FD", "#1565C0"),
    ("3", "Cek skor", "Target\n≥ 12/15", "#FFF8E1", "#F9A825"),
    ("4", "Centang\nchecklist", "10/10 +\nfoto wiring", "#FCE4EC", "#C62828"),
]
for i, (num, title, body, fill, out) in enumerate(steps):
    x0 = 40 + i * 340
    box(d, (x0, 150, x0 + 310, 480), fill, out, 4)
    box(d, (x0 + 20, 175, x0 + 90, 245), "#FFFFFF", out, 3)
    center(d, x0 + 55, 210, num, FT, out)
    for j, ln in enumerate(title.split("\n")):
        d.text((x0 + 108, 185 + j * 36), ln, font=FH, fill="#1a1a1a")
    for j, ln in enumerate(body.split("\n")):
        d.text((x0 + 28, 320 + j * 36), ln, font=F, fill="#333")
box(d, (40, 520, 1360, 610), "#FFFFFF", "#1a1a1a", 3)
center(d, 700, 555, "Tidak perlu hari ini: Arduino IDE · Upload · Laragon · php artisan · Wi-Fi sketch", F, "#333")
tl.save(OUT / "fs-gate-builder-tools.png", optimize=True)
print("tools", (OUT / "fs-gate-builder-tools.png").stat().st_size)

# --- Criteria ---
cr = Image.new("RGB", (1200, 680), "#F5F5F0")
d = ImageDraw.Draw(cr)
box(d, (20, 16, 1180, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 40, "Kriteria lulus BUILDER (ringkas)", FT)
center(d, 600, 75, "Sebelum Wi-Fi: fondasi lokal sudah kuat", F, "#333")
items = [
    ("1", "Automasi lokal", "Sensor → keputusan → relay/LED"),
    ("2", "OLED / Serial", "Angka sensor terbaca"),
    ("3", "Relay aman", "Beban kecil · kaki COM/NO"),
    ("4", "Peta pin", "DevKitC-1 · hindari pin berbahaya"),
    ("5", "Foto wiring", "Dokumentasi meja sendiri"),
    ("6", "Kuis ≥12/15", "Istilah BUILDER cocok"),
]
for i, (num, title, tip) in enumerate(items):
    row, col = divmod(i, 2)
    x0 = 40 + col * 580
    y0 = 130 + row * 160
    box(d, (x0, y0, x0 + 540, y0 + 140), "#E8F5E9", "#2E7D32", 3)
    box(d, (x0 + 16, y0 + 20, x0 + 70, y0 + 74), "#FFFFFF", "#2E7D32", 3)
    center(d, x0 + 43, y0 + 47, num, FT, "#2E7D32")
    d.text((x0 + 90, y0 + 30), title, font=FH, fill="#1a1a1a")
    d.text((x0 + 90, y0 + 80), tip, font=F, fill="#333")
box(d, (40, 610, 1160, 660), "#FFFFFF", "#1a1a1a", 3)
center(d, 600, 635, "Sumber: diagram Koding Indonesia · Gate BUILDER → CONNECTED", FS, "#333")
cr.save(OUT / "fs-gate-builder-criteria.png", optimize=True)
print("criteria", (OUT / "fs-gate-builder-criteria.png").stat().st_size)

# --- Success ---
suc = Image.new("RGB", (1200, 480), "#F5F5F0")
d = ImageDraw.Draw(suc)
box(d, (20, 16, 1180, 90), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 53, "Sukses = skor kuis ≥ 12/15", FT)
box(d, (60, 130, 560, 420), "#E8F5E9", "#2E7D32", 4)
center(d, 310, 200, "Lulus gate", FT, "#1B5E20")
center(d, 310, 270, "Siap fase CONNECTED", FH, "#1a1a1a")
center(d, 310, 340, "Wi-Fi · HTTP · MQTT …", F, "#333")
box(d, (640, 130, 1140, 420), "#FFEBEE", "#C62828", 4)
center(d, 890, 200, "Belum lulus", FT, "#B71C1C")
center(d, 890, 270, "Baca ulang istilah", FH, "#1a1a1a")
center(d, 890, 340, "Ulangi kuis — biasa", F, "#333")
suc.save(OUT / "fs-gate-builder-success.png", optimize=True)
print("success", (OUT / "fs-gate-builder-success.png").stat().st_size)
print("done")
