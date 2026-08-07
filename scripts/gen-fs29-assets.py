# -*- coding: utf-8 -*-
"""Generate FS-29 / #99 cover + diagrams (Wi-Fi dari nol)."""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)
TMP = Path("tmp-qa99")
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
    im = Image.open(src).convert("RGBA")
    bg = Image.new("RGB", size, "#FFFFFF")
    im.thumbnail(size, Image.Resampling.LANCZOS)
    canvas = Image.new("RGBA", size, (255, 255, 255, 255))
    canvas.alpha_composite(im, ((size[0] - im.width) // 2, (size[1] - im.height) // 2))
    bg.paste(canvas.convert("RGB"))
    return bg


# --- Cover ---
cover = Image.new("RGB", (1200, 675), "#0D47A1")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#1565C0", "#0D47A1", "#002171"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

box(d, (70, 70, 530, 330), "#E3F2FD", "#FFFFFF", 4, 16)
center(d, 300, 140, "ESP32", FT, "#0D47A1")
center(d, 300, 210, "station mode", FH, "#1a1a1a")
center(d, 300, 270, "WiFi.begin", FB, "#333")

box(d, (670, 70, 1130, 330), "#E8F5E9", "#FFFFFF", 4, 16)
center(d, 900, 140, "Router", FT, "#1B5E20")
center(d, 900, 210, "SSID 2,4 GHz", FH, "#1a1a1a")
center(d, 900, 270, "beri IP", FB, "#333")

center(d, 600, 400, "FS-29", FT, "#BBDEFB")
center(d, 600, 470, "Wi-Fi dari nol: SSID, IP, gagal connect", FT, "#FFFFFF")
center(d, 600, 545, "Serial Monitor · baud 115200 · dapat IP valid", FB, "#E3F2FD")
center(d, 600, 610, "Arduino IDE · Upload · tanpa Library Manager ekstra", FH, "#90CAF9")
cover.save(OUT / "fs29-cover-wifi.jpg", quality=88, optimize=True)
cover.save(OUT / "fs29-cover-wifi.webp", "WEBP", quality=85)
print("cover", (OUT / "fs29-cover-wifi.jpg").stat().st_size)

# --- Tools order ---
tl = Image.new("RGB", (1400, 720), "#F5F5F0")
d = ImageDraw.Draw(tl)
box(d, (20, 16, 1380, 130), "#FFFFFF", "#1a1a1a", 4)
center(d, 700, 50, "Urutan tools hari ini — IDE dulu, bukan Laragon", FT)
center(d, 700, 100, "Catat SSID 2,4 GHz → isi sketch → Verify → Upload → Serial 115200", FB, "#333")
order = [
    ("1", "Siapkan\nSSID", "Wi-Fi rumah\n2,4 GHz", "#E8F5E9", "#2E7D32"),
    ("2", "Buka\nArduino IDE", "Board ESP32\nDev Module", "#E3F2FD", "#1565C0"),
    ("3", "Upload\nsketch", "FS29_wifi_begin\n+ Serial Monitor", "#FFF8E1", "#F9A825"),
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
center(d, 700, 655, "Laragon  ·  php artisan  ·  MQTT  ·  Library Manager ekstra  ·  breadboard sensor", FB, "#333")
tl.save(OUT / "fs29-tools-ide.png", optimize=True)
print("tools", (OUT / "fs29-tools-ide.png").stat().st_size)

# --- Core note (WiFi.h built-in) ---
lib = Image.new("RGB", (1200, 560), "#F5F5F0")
d = ImageDraw.Draw(lib)
box(d, (20, 16, 1180, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 48, "WiFi.h sudah di core ESP32 — tanpa Library Manager ekstra", FT)
center(d, 600, 92, "Beda dari FS-28 (BME280/OLED butuh Adafruit). Hari ini cukup #include <WiFi.h>", F, "#333")
steps = [
    ("1", "Sketch baru", "File → New\nSimpan FS29_wifi_begin", "#E3F2FD", "#1565C0"),
    ("2", "Isi SSID", "Ganti YOUR_SSID\n& YOUR_PASS", "#E8F5E9", "#2E7D32"),
    ("3", "Upload", "Verify → Upload\n→ Serial 115200", "#FFF8E1", "#F9A825"),
]
for i, (num, title, body, fill, out) in enumerate(steps):
    x0 = 40 + i * 390
    box(d, (x0, 150, x0 + 360, 480), fill, out, 4)
    box(d, (x0 + 24, 175, x0 + 96, 247), "#FFFFFF", out, 3)
    center(d, x0 + 60, 211, num, FT, out)
    d.text((x0 + 118, 195), title, font=FH, fill="#1a1a1a")
    for j, ln in enumerate(body.split("\n")):
        d.text((x0 + 36, 300 + j * 40), ln, font=F, fill="#333")
    if i < 2:
        center(d, x0 + 375, 315, "→", FT, "#1a1a1a")
lib.save(OUT / "fs29-wifi-core.png", optimize=True)
print("core", (OUT / "fs29-wifi-core.png").stat().st_size)

# --- Network schema (main figure style helper) ---
W, H = 1200, 720
img = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(img)
box(d, (20, 16, W - 20, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 48, "Gambar utama — ESP32 gabung Wi-Fi rumah (FS-29)", FT)
center(d, W // 2, 92, "Station mode · SSID 2,4 GHz · router beri IP · bukti di Serial", F, "#333")

box(d, (60, 170, 380, 480), "#FFF8E1", "#F9A825", 4)
center(d, 220, 230, "ESP32", FT, "#F57F17")
center(d, 220, 290, "station", FH, "#1a1a1a")
center(d, 220, 350, "WiFi.begin", FB, "#333")
center(d, 220, 410, "minta masuk", FS, "#333")

d.line([(380, 325), (560, 325)], fill="#1565C0", width=8)
center(d, 470, 290, "2,4 GHz", FH, "#1565C0")

box(d, (560, 170, 900, 480), "#BBDEFB", "#1565C0", 4)
center(d, 730, 230, "Router", FT, "#0D47A1")
center(d, 730, 290, "SSID + password", FH, "#1a1a1a")
center(d, 730, 350, "DHCP → IP", FB, "#333")
center(d, 730, 410, "bukan 5 GHz only", FS, "#333")

d.line([(900, 325), (1080, 325)], fill="#2E7D32", width=8)

box(d, (960, 220, 1140, 430), "#C8E6C9", "#2E7D32", 4)
center(d, 1050, 280, "Serial", FH, "#1B5E20")
center(d, 1050, 340, "IP valid", FB, "#1a1a1a")
center(d, 1050, 390, "115200", FS, "#333")

box(d, (40, 520, W - 40, 690), "#E3F2FD", "#1565C0", 3)
center(d, W // 2, 575, "Sukses = Serial menampilkan IP (contoh 192.168.x.x) — bukan 'localhost' di HP", F, "#0D47A1")
center(d, W // 2, 635, "Sumber: diagram Koding Indonesia (FS-29) · konsep Wi-Fi station: dokumentasi Espressif", FS, "#0D47A1")
img.save(OUT / "fs29-wifi-station.png", optimize=True)
print("station", (OUT / "fs29-wifi-station.png").stat().st_size)

# --- Skema bantu: 2.4 vs 5 ---
sk = Image.new("RGB", (1200, 620), "#F5F5F0")
d = ImageDraw.Draw(sk)
box(d, (20, 16, 1180, 110), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 45, "Skema bantu — pilih jaringan yang ESP32 bisa dengar", FT)
center(d, 600, 85, "ESP32 tipikal = Wi-Fi 2,4 GHz (bukan 5 GHz only)", F, "#333")

box(d, (60, 150, 560, 480), "#E8F5E9", "#2E7D32", 4)
center(d, 310, 210, "Boleh / cocok", FT, "#1B5E20")
for j, ln in enumerate(["SSID 2,4 GHz", "Hotspot HP (2,4)", "Password benar", "Timeout cukup sabar"]):
    center(d, 310, 290 + j * 42, "✓  " + ln, FH, "#1a1a1a")

box(d, (640, 150, 1140, 480), "#FFEBEE", "#C62828", 4)
center(d, 890, 210, "Sering gagal", FT, "#B71C1C")
for j, ln in enumerate(["SSID 5 GHz only", "Password salah", "Timeout terlalu singkat", "Mengira localhost = ESP32"]):
    center(d, 890, 290 + j * 42, "✗  " + ln, FH, "#1a1a1a")

box(d, (40, 510, 1160, 590), "#FFFFFF", "#1a1a1a", 3)
center(d, 600, 550, "Sumber: diagram Koding Indonesia (FS-29) · cek band di aplikasi router / label SSID", F, "#333")
sk.save(OUT / "fs29-band-2g4.png", optimize=True)
print("band", (OUT / "fs29-band-2g4.png").stat().st_size)

# --- Modul / router cite ---
MW, MH = 1400, 620
mod = Image.new("RGB", (MW, MH), "#F5F5F0")
d = ImageDraw.Draw(mod)
box(d, (20, 16, MW - 20, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, MW // 2, 48, "Kenali dulu — router rumah & ESP32 station", FT)
center(d, MW // 2, 92, "Router (foto Commons) · ESP32 hanya 'gabung' jaringan · bukti di Serial", F, "#333")

router = TMP / "router-tplink.png"
box(d, (40, 145, 520, 505), "#E3F2FD", "#1565C0", 4)
if router.is_file():
    mod.paste(fit_cover(router, (420, 260)), (70, 170))
else:
    center(d, 280, 280, "Router", FT, "#1565C0")
center(d, 280, 460, "Router Wi-Fi rumah", FB, "#1a1a1a")

box(d, (560, 145, 980, 505), "#FFF8E1", "#F9A825", 4)
center(d, 770, 230, "ESP32", FT, "#F57F17")
center(d, 770, 300, "mode station", FH, "#1a1a1a")
center(d, 770, 360, "seperti HP", FB, "#333")
center(d, 770, 420, "yang gabung SSID", FS, "#333")
center(d, 770, 470, "USB data + antena board", FS, "#333")

box(d, (1020, 145, 1360, 505), "#E8F5E9", "#2E7D32", 4)
center(d, 1190, 230, "Bukti sukses", FT, "#1B5E20")
for j, ln in enumerate(["Serial 115200", "WL_CONNECTED", "IP 192.168.x.x", "RSSI (opsional)"]):
    center(d, 1190, 310 + j * 42, ln, FH, "#1a1a1a")

box(d, (40, 530, MW - 40, 590), "#FFFFFF", "#1a1a1a", 3)
center(d, MW // 2, 560, "Sitasi router di caption artikel · kolase: Koding Indonesia (FS-29)", F, "#333")
mod.save(OUT / "fs29-modul-router.png", optimize=True)
print("modul", (OUT / "fs29-modul-router.png").stat().st_size)

# --- Success Serial ---
ok = Image.new("RGB", (1200, 520), "#F5F5F0")
d = ImageDraw.Draw(ok)
box(d, (20, 16, 1180, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 58, "Sukses = Serial menampilkan IP valid (bukan gagal terus)", FT)

box(d, (40, 130, 580, 470), "#263238", "#1a1a1a", 4)
center(d, 310, 170, "Serial Monitor · 115200", FH, "#80CBC4")
lines = [
    "FS29_wifi_begin ready",
    "Menghubungkan ke Wi-Fi...",
    "WL_CONNECTED",
    "IP: 192.168.1.42",
    "RSSI: -51 dBm",
]
for i, ln in enumerate(lines):
    d.text((70, 220 + i * 40), ln, font=F, fill="#FFF59D" if i >= 3 else "#80CBC4")

box(d, (620, 130, 1160, 470), "#FFEBEE", "#C62828", 4)
center(d, 890, 190, "Belum sukses", FH, "#B71C1C")
fail = [
    "...... (titik terus)",
    "timeout — cek SSID",
    "password salah?",
    "SSID 5 GHz only?",
]
for i, ln in enumerate(fail):
    d.text((660, 250 + i * 40), ln, font=F, fill="#C62828")
ok.save(OUT / "fs29-success-serial.png", optimize=True)
print("success", (OUT / "fs29-success-serial.png").stat().st_size)

print("done")
