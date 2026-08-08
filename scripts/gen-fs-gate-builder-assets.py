# -*- coding: utf-8 -*-
"""Regenerate Gate BUILDER assets + relay contacts diagram (QA awam)."""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 38)
    FH = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 26)
    FB = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 22)
    F = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 20)
    FS = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 17)
except OSError:
    FT = FH = FB = F = FS = ImageFont.load_default()


def center(d, cx, cy, text, font, fill="#1a1a1a"):
    d.text((cx, cy), text, font=font, fill=fill, anchor="mm")


def box(d, xy, fill, outline, w=4, r=14):
    d.rounded_rectangle(xy, radius=r, fill=fill, outline=outline, width=w)


# --- Cover (split long subtitle so text never clips) ---
cover = Image.new("RGB", (1200, 675), "#1B5E20")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#43A047", "#2E7D32", "#1B5E20"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

box(d, (50, 55, 400, 295), "#E8F5E9", "#FFFFFF", 4, 16)
center(d, 225, 115, "BUILDER", FT, "#1B5E20")
center(d, 225, 175, "sensor lokal", FH, "#1a1a1a")
center(d, 225, 230, "tanpa internet", FB, "#333")

box(d, (440, 95, 760, 255), "#FFF8E1", "#FFFFFF", 4, 16)
center(d, 600, 145, "Gate", FT, "#F57F17")
center(d, 600, 205, "kuis >= 12/15", FH, "#1a1a1a")

box(d, (800, 55, 1150, 295), "#E3F2FD", "#FFFFFF", 4, 16)
center(d, 975, 115, "CONNECTED", FT, "#0D47A1")
center(d, 975, 175, "Wi-Fi · HTTP", FH, "#1a1a1a")
center(d, 975, 230, "MQTT ...", FB, "#333")

center(d, 600, 385, "Gate BUILDER", FT, "#C8E6C9")
center(d, 600, 445, "Pintu naik ke CONNECTED", FT, "#FFFFFF")
center(d, 600, 510, "Kuis matching di browser", FH, "#E8F5E9")
center(d, 600, 555, "Target lulus: 12 dari 15", FH, "#E8F5E9")
center(d, 600, 610, "Tanpa Laragon · tanpa Upload sketch hari ini", FS, "#A5D6A7")
cover.save(OUT / "fs-gate-builder-cover.jpg", quality=90, optimize=True)
cover.save(OUT / "fs-gate-builder-cover.webp", "WEBP", quality=88)
print("cover ok")

# --- Tools ---
tl = Image.new("RGB", (1400, 640), "#F5F5F0")
d = ImageDraw.Draw(tl)
box(d, (20, 16, 1380, 110), "#FFFFFF", "#1a1a1a", 4)
center(d, 700, 42, "Urutan tools hari ini — browser saja", FT)
center(d, 700, 82, "Baca kriteria → kerjakan kuis → Cek skor → checklist", FB, "#333")
steps = [
    ("1", "Buka\nbrowser", "Halaman artikel\nGate BUILDER", "#E8F5E9", "#2E7D32"),
    ("2", "Kerjakan\nkuis", "15 istilah\ncocokkan arti", "#E3F2FD", "#1565C0"),
    ("3", "Cek\nskor", "Target lulus\n>= 12/15", "#FFF8E1", "#F9A825"),
    ("4", "Centang\nchecklist", "10/10 +\nfoto wiring", "#FCE4EC", "#C62828"),
]
for i, (num, title, body, fill, out) in enumerate(steps):
    x0 = 40 + i * 340
    box(d, (x0, 150, x0 + 310, 480), fill, out, 4)
    box(d, (x0 + 20, 175, x0 + 90, 245), "#FFFFFF", out, 3)
    center(d, x0 + 55, 210, num, FT, out)
    for j, ln in enumerate(title.split("\n")):
        d.text((x0 + 108, 185 + j * 34), ln, font=FH, fill="#1a1a1a")
    for j, ln in enumerate(body.split("\n")):
        d.text((x0 + 28, 320 + j * 34), ln, font=F, fill="#333")
box(d, (40, 520, 1360, 610), "#FFFFFF", "#1a1a1a", 3)
center(d, 700, 555, "Tidak perlu hari ini: Arduino IDE · Upload · Laragon · php artisan · Wi-Fi", F, "#333")
tl.save(OUT / "fs-gate-builder-tools.png", optimize=True)
print("tools ok")

# --- Criteria ---
cr = Image.new("RGB", (1200, 700), "#F5F5F0")
d = ImageDraw.Draw(cr)
box(d, (20, 16, 1180, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 40, "Kriteria lulus BUILDER (ringkas)", FT)
center(d, 600, 75, "Sebelum Wi-Fi: fondasi lokal sudah kuat", F, "#333")
items = [
    ("1", "Automasi lokal", "Sensor → keputusan → relay/LED"),
    ("2", "OLED / Serial", "Angka sensor terbaca jelas"),
    ("3", "Relay aman", "Beban kecil · kenal COM/NO/NC"),
    ("4", "Peta pin", "DevKitC-1 · hindari pin berbahaya"),
    ("5", "Foto wiring", "Dokumentasi meja sendiri"),
    ("6", "Kuis >= 12/15", "Istilah BUILDER cocok"),
]
for i, (num, title, tip) in enumerate(items):
    row, col = divmod(i, 2)
    x0 = 40 + col * 580
    y0 = 130 + row * 165
    box(d, (x0, y0, x0 + 540, y0 + 145), "#E8F5E9", "#2E7D32", 3)
    box(d, (x0 + 16, y0 + 22, x0 + 70, y0 + 76), "#FFFFFF", "#2E7D32", 3)
    center(d, x0 + 43, y0 + 49, num, FT, "#2E7D32")
    d.text((x0 + 90, y0 + 32), title, font=FH, fill="#1a1a1a")
    d.text((x0 + 90, y0 + 85), tip, font=F, fill="#333")
box(d, (40, 635, 1160, 680), "#FFFFFF", "#1a1a1a", 3)
center(d, 600, 657, "Sumber: diagram Koding Indonesia · Gate BUILDER → CONNECTED", FS, "#333")
cr.save(OUT / "fs-gate-builder-criteria.png", optimize=True)
print("criteria ok")

# --- Success ---
suc = Image.new("RGB", (1200, 480), "#F5F5F0")
d = ImageDraw.Draw(suc)
box(d, (20, 16, 1180, 90), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 53, "Sukses = skor kuis >= 12/15", FT)
box(d, (60, 130, 560, 420), "#E8F5E9", "#2E7D32", 4)
center(d, 310, 200, "Lulus gate", FT, "#1B5E20")
center(d, 310, 270, "Siap fase CONNECTED", FH, "#1a1a1a")
center(d, 310, 340, "Wi-Fi · HTTP · MQTT ...", F, "#333")
box(d, (640, 130, 1140, 420), "#FFEBEE", "#C62828", 4)
center(d, 890, 200, "Belum lulus", FT, "#B71C1C")
center(d, 890, 270, "Baca ulang istilah", FH, "#1a1a1a")
center(d, 890, 340, "Ulangi kuis — biasa", F, "#333")
suc.save(OUT / "fs-gate-builder-success.png", optimize=True)
print("success ok")

# --- Relay NC/COM/NO simple diagram ---
rel = Image.new("RGB", (1200, 560), "#F5F5F0")
d = ImageDraw.Draw(rel)
box(d, (20, 16, 1180, 95), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 40, "Relay: tiga kaki kontak (ingat singkat)", FT)
center(d, 600, 72, "COM = kaki bersama · NO/NC = jalur yang terbuka atau tertutup", F, "#333")

# COM center
box(d, (480, 200, 720, 300), "#FFF8E1", "#F9A825", 4)
center(d, 600, 235, "COM", FT, "#F57F17")
center(d, 600, 275, "kaki bersama", F, "#333")

# NC left
box(d, (80, 160, 360, 340), "#E3F2FD", "#1565C0", 4)
center(d, 220, 210, "NC", FT, "#0D47A1")
center(d, 220, 255, "Normally Closed", FH, "#1a1a1a")
center(d, 220, 300, "tersambung ke COM", F, "#333")
center(d, 220, 325, "saat relay diam", F, "#333")

# NO right
box(d, (840, 160, 1120, 340), "#E8F5E9", "#2E7D32", 4)
center(d, 980, 210, "NO", FT, "#1B5E20")
center(d, 980, 255, "Normally Open", FH, "#1a1a1a")
center(d, 980, 300, "tersambung ke COM", F, "#333")
center(d, 980, 325, "saat relay aktif", F, "#333")

# arrows
d.line((360, 250, 480, 250), fill="#1a1a1a", width=4)
d.line((720, 250, 840, 250), fill="#1a1a1a", width=4)

box(d, (60, 390, 1140, 520), "#FFFFFF", "#1a1a1a", 3)
center(d, 600, 430, "Analogi saklar lampu: COM = tuas bersama; NO/NC = dua jalur yang dipilih.", F, "#1a1a1a")
center(d, 600, 470, "Sumber: diagram Koding Indonesia (Gate BUILDER) · istilah standar kontak relay.", FS, "#333")
rel.save(OUT / "fs-gate-builder-relay-contacts.png", optimize=True)
print("relay ok")
print("done")
