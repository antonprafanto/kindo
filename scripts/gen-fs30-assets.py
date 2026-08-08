# -*- coding: utf-8 -*-
"""Generate FS-30 / #100 cover + diagrams (HTTP & JSON bahasa manusia)."""
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

OUT = Path("public/images/fsiot")
OUT.mkdir(parents=True, exist_ok=True)
TMP = Path("tmp-qa100")
TMP.mkdir(parents=True, exist_ok=True)

try:
    FT = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 40)
    FH = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 30)
    FB = ImageFont.truetype("C:/Windows/Fonts/segoeuib.ttf", 26)
    F = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 24)
    FS = ImageFont.truetype("C:/Windows/Fonts/segoeui.ttf", 20)
    FM = ImageFont.truetype("C:/Windows/Fonts/consola.ttf", 22)
except OSError:
    FT = FH = FB = F = FS = FM = ImageFont.load_default()


def center(d, cx, cy, text, font, fill="#1a1a1a"):
    d.text((cx, cy), text, font=font, fill=fill, anchor="mm")


def box(d, xy, fill, outline, w=4, r=14):
    d.rounded_rectangle(xy, radius=r, fill=fill, outline=outline, width=w)


# --- Cover ---
cover = Image.new("RGB", (1200, 675), "#1A237E")
d = ImageDraw.Draw(cover)
for i, c in enumerate(["#3949AB", "#283593", "#1A237E"]):
    d.rectangle((0, i * 225, 1200, (i + 1) * 225), fill=c)

box(d, (60, 60, 420, 320), "#E8EAF6", "#FFFFFF", 4, 16)
center(d, 240, 120, "Browser", FT, "#1A237E")
center(d, 240, 185, "lihat JSON", FH, "#1a1a1a")
center(d, 240, 250, "dulu", FB, "#333")

box(d, (450, 60, 810, 320), "#E3F2FD", "#FFFFFF", 4, 16)
center(d, 630, 120, "HTTP GET", FT, "#0D47A1")
center(d, 630, 185, "minta data", FH, "#1a1a1a")
center(d, 630, 250, "lewat URL", FB, "#333")

box(d, (840, 60, 1140, 320), "#E8F5E9", "#FFFFFF", 4, 16)
center(d, 990, 120, "Serial", FT, "#1B5E20")
center(d, 990, 185, "cetak JSON", FH, "#1a1a1a")
center(d, 990, 250, "115200", FB, "#333")

center(d, 600, 400, "FS-30", FT, "#C5CAE9")
center(d, 600, 460, "HTTP & JSON bahasa manusia", FT, "#FFFFFF")
center(d, 600, 520, "URL · GET · status 200 · kurung kurawal", FH, "#E8EAF6")
center(d, 600, 575, "Browser dulu · lalu Arduino IDE + Serial", FH, "#9FA8DA")
center(d, 600, 630, "HTTPClient di core — tanpa Library Manager ekstra", FS, "#C5CAE9")
cover.save(OUT / "fs30-cover-http.jpg", quality=88, optimize=True)
cover.save(OUT / "fs30-cover-http.webp", "WEBP", quality=85)
print("cover", (OUT / "fs30-cover-http.jpg").stat().st_size)

# --- Tools order (browser FIRST) ---
tl = Image.new("RGB", (1400, 720), "#F5F5F0")
d = ImageDraw.Draw(tl)
box(d, (20, 16, 1380, 120), "#FFFFFF", "#1a1a1a", 4)
center(d, 700, 42, "Urutan tools hari ini — browser dulu", FT)
center(d, 700, 88, "Lihat JSON di browser → IDE → Upload → Serial 115200", FB, "#333")
order = [
    ("1", "Buka\nbrowser", "Lihat URL demo\nJSON di layar", "#E8EAF6", "#3949AB"),
    ("2", "Buka\nArduino IDE", "Board ESP32\n+ Wi-Fi (FS-29)", "#E3F2FD", "#1565C0"),
    ("3", "Upload\nsketch", "FS30_http_get\n+ Serial Monitor", "#FFF8E1", "#F9A825"),
    ("4", "Centang\nchecklist", "10/10 di\nbrowser artikel", "#FCE4EC", "#C62828"),
]
for i, (num, title, body, fill, out) in enumerate(order):
    x0 = 40 + i * 340
    box(d, (x0, 160, x0 + 310, 510), fill, out, 4)
    box(d, (x0 + 20, 185, x0 + 90, 255), "#FFFFFF", out, 3)
    center(d, x0 + 55, 220, num, FT, out)
    for j, ln in enumerate(title.split("\n")):
        d.text((x0 + 108, 195 + j * 38), ln, font=FH, fill="#1a1a1a")
    for j, ln in enumerate(body.split("\n")):
        d.text((x0 + 28, 330 + j * 38), ln, font=F, fill="#333")
box(d, (40, 545, 1360, 690), "#FFFFFF", "#1a1a1a", 3)
center(d, 700, 590, "Tidak perlu hari ini:", FH, "#1a1a1a")
center(d, 700, 645, "Laragon  ·  php artisan  ·  MQTT  ·  ArduinoJson  ·  Library Manager ekstra", FB, "#333")
tl.save(OUT / "fs30-tools-order.png", optimize=True)
print("tools", (OUT / "fs30-tools-order.png").stat().st_size)

# --- Core note ---
lib = Image.new("RGB", (1200, 560), "#F5F5F0")
d = ImageDraw.Draw(lib)
box(d, (20, 16, 1180, 110), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 40, "HTTPClient sudah di core ESP32", FT)
center(d, 600, 82, "Cukup #include <WiFi.h> + <HTTPClient.h> — tanpa Library Manager", F, "#333")
steps = [
    ("1", "Browser", "Buka URL demo\nlihat JSON", "#E8EAF6", "#3949AB"),
    ("2", "Sketch", "FS30_http_get\nSSID + URL", "#E3F2FD", "#1565C0"),
    ("3", "Serial", "Kode 200 +\nteks JSON", "#E8F5E9", "#2E7D32"),
]
for i, (num, title, body, fill, out) in enumerate(steps):
    x0 = 60 + i * 380
    box(d, (x0, 150, x0 + 340, 430), fill, out, 4)
    box(d, (x0 + 20, 175, x0 + 90, 245), "#FFFFFF", out, 3)
    center(d, x0 + 55, 210, num, FT, out)
    center(d, x0 + 200, 210, title, FH)
    for j, ln in enumerate(body.split("\n")):
        center(d, x0 + 170, 300 + j * 40, ln, F, "#333")
    if i < 2:
        d.polygon([(x0 + 350, 290), (x0 + 370, 305), (x0 + 350, 320)], fill="#1a1a1a")
box(d, (40, 460, 1160, 530), "#FFFFFF", "#1a1a1a", 3)
center(d, 600, 495, "Sumber: diagram Koding Indonesia (FS-30) · API demo: JSONPlaceholder", FS, "#333")
lib.save(OUT / "fs30-http-core.png", optimize=True)
print("core", (OUT / "fs30-http-core.png").stat().st_size)

# --- Main figure: HTTP GET flow ---
W, H = 1200, 720
main = Image.new("RGB", (W, H), "#F5F5F0")
d = ImageDraw.Draw(main)
box(d, (20, 16, W - 20, 110), "#FFFFFF", "#1a1a1a", 4)
center(d, W // 2, 42, "Gambar utama — ESP32 minta data lewat HTTP GET", FT)
center(d, W // 2, 82, "Seperti ketik URL di browser · server balas JSON · cetak di Serial", F, "#333")

box(d, (50, 150, 350, 460), "#FFF8E1", "#F9A825", 4)
center(d, 200, 200, "ESP32", FT, "#F57F17")
center(d, 200, 260, "HTTP GET", FH, "#1a1a1a")
center(d, 200, 320, "minta data", FB, "#333")
center(d, 200, 380, "ke URL", FS, "#333")

d.line([(350, 300), (500, 300)], fill="#3949AB", width=8)
center(d, 425, 265, "minta", FH, "#3949AB")

box(d, (500, 150, 830, 460), "#E8EAF6", "#3949AB", 4)
center(d, 665, 200, "Server", FT, "#1A237E")
center(d, 665, 260, "internet", FH, "#1a1a1a")
center(d, 665, 320, "status 200", FB, "#333")
center(d, 665, 380, "+ isi JSON", FS, "#333")

d.line([(830, 300), (980, 300)], fill="#2E7D32", width=8)
center(d, 905, 265, "balas", FH, "#2E7D32")

box(d, (960, 200, 1150, 420), "#C8E6C9", "#2E7D32", 4)
center(d, 1055, 260, "Serial", FH, "#1B5E20")
center(d, 1055, 320, "JSON teks", FB, "#1a1a1a")
center(d, 1055, 370, "115200", FS, "#333")

box(d, (40, 500, W - 40, 690), "#E8EAF6", "#3949AB", 3)
center(d, W // 2, 555, "Sukses = Serial menampilkan kode 200 + teks berisi { dan }", F, "#1A237E")
center(d, W // 2, 615, "Sumber: diagram Koding Indonesia (FS-30) · acuan: MDN HTTP + JSONPlaceholder", FS, "#1A237E")
main.save(OUT / "fs30-http-get.png", optimize=True)
print("main", (OUT / "fs30-http-get.png").stat().st_size)

# --- Skema bantu: JSON anatomy ---
js = Image.new("RGB", (1200, 640), "#F5F5F0")
d = ImageDraw.Draw(js)
box(d, (20, 16, 1180, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 40, "Skema bantu — anatomi JSON sederhana", FT)
center(d, 600, 75, "Kurung kurawal · kunci (key) · nilai (value) · tanda kutip", F, "#333")

box(d, (80, 140, 1120, 480), "#263238", "#1a1a1a", 4)
lines = [
    ("{", "#90CAF9"),
    ('  "id": 1,', "#FFF59D"),
    ('  "title": "contoh tugas",', "#A5D6A7"),
    ('  "completed": false', "#FFAB91"),
    ("}", "#90CAF9"),
]
for i, (ln, col) in enumerate(lines):
    d.text((140, 180 + i * 50), ln, font=FM, fill=col)

box(d, (80, 510, 1120, 610), "#FFFFFF", "#1a1a1a", 3)
center(d, 600, 540, "Intinya: JSON = catatan rapi dengan pasangan nama:nilai", F, "#1a1a1a")
center(d, 600, 580, "Sumber: diagram Koding Indonesia (FS-30) · konsep: json.org / MDN JSON", FS, "#333")
js.save(OUT / "fs30-json-anatomy.png", optimize=True)
print("json", (OUT / "fs30-json-anatomy.png").stat().st_size)

# --- Status codes ---
st = Image.new("RGB", (1200, 560), "#F5F5F0")
d = ImageDraw.Draw(st)
box(d, (20, 16, 1180, 100), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 40, "Kode status HTTP — bahasa manusia", FT)
center(d, 600, 75, "Angka dari server: berhasil / tidak ketemu / server lagi masalah", F, "#333")
codes = [
    ("200", "OK", "Berhasil — data datang", "#E8F5E9", "#2E7D32"),
    ("404", "Tidak ketemu", "Alamat / URL salah", "#FFF8E1", "#F9A825"),
    ("500", "Server bermasalah", "Bukan salah ESP32", "#FFEBEE", "#C62828"),
]
for i, (code, name, tip, fill, out) in enumerate(codes):
    x0 = 50 + i * 380
    box(d, (x0, 140, x0 + 350, 420), fill, out, 4)
    center(d, x0 + 175, 210, code, FT, out)
    center(d, x0 + 175, 280, name, FH, "#1a1a1a")
    center(d, x0 + 175, 350, tip, F, "#333")
box(d, (40, 460, 1160, 530), "#FFFFFF", "#1a1a1a", 3)
center(d, 600, 495, "Sumber: diagram Koding Indonesia (FS-30) · acuan: MDN HTTP status codes", FS, "#333")
st.save(OUT / "fs30-status-codes.png", optimize=True)
print("status", (OUT / "fs30-status-codes.png").stat().st_size)

# --- Success serial ---
suc = Image.new("RGB", (1200, 520), "#F5F5F0")
d = ImageDraw.Draw(suc)
box(d, (20, 16, 1180, 90), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 53, "Sukses = Serial tampilkan 200 + JSON", FT)

box(d, (50, 120, 700, 480), "#263238", "#1a1a1a", 4)
d.text((80, 150), "Serial Monitor · 115200", font=FH, fill="#80CBC4")
for i, (ln, col) in enumerate([
    ("FS30_http_get ready", "#B0BEC5"),
    ("Wi-Fi OK", "#B0BEC5"),
    ("GET .../todos/1", "#B0BEC5"),
    ("HTTP 200", "#A5D6A7"),
    ('{"userId":1,"id":1,...}', "#FFF59D"),
]):
    d.text((80, 210 + i * 42), ln, font=FM, fill=col)

box(d, (740, 120, 1150, 480), "#FFEBEE", "#C62828", 4)
center(d, 945, 170, "Belum sukses", FH, "#B71C1C")
for j, ln in enumerate([
    "Wi-Fi belum (FS-29)",
    "HTTP -1 / gagal SSL",
    "404 — URL salah",
    "JSON kosong / timeout",
]):
    center(d, 945, 250 + j * 45, ln, F, "#1a1a1a")
suc.save(OUT / "fs30-success-serial.png", optimize=True)
print("success", (OUT / "fs30-success-serial.png").stat().st_size)

# --- Browser mock: what JSON looks like in Chrome-like window ---
br = Image.new("RGB", (1200, 700), "#F5F5F0")
d = ImageDraw.Draw(br)
box(d, (20, 16, 1180, 90), "#FFFFFF", "#1a1a1a", 4)
center(d, 600, 53, "Latihan mata — JSON di browser (seperti ini)", FT)

# chrome bar
box(d, (60, 120, 1140, 640), "#FFFFFF", "#1a1a1a", 4)
d.rectangle((60, 120, 1140, 175), fill="#E8EAF6")
center(d, 140, 148, "● ● ●", F, "#9E9E9E")
box(d, (220, 135, 1080, 165), "#FFFFFF", "#9E9E9E", 2, 8)
d.text((235, 140), "jsonplaceholder.typicode.com/todos/1", font=FS, fill="#333")

# body JSON
body_lines = [
    ("{", "#1565C0"),
    ('  "userId": 1,', "#6A1B9A"),
    ('  "id": 1,', "#6A1B9A"),
    ('  "title": "delectus aut autem",', "#2E7D32"),
    ('  "completed": false', "#C62828"),
    ("}", "#1565C0"),
]
for i, (ln, col) in enumerate(body_lines):
    d.text((100, 210 + i * 48), ln, font=FM, fill=col)

d.text((100, 520), "Yang dicari mata: ada {  dan  }  · ada \"id\" · ada tanda kutip", font=F, fill="#333")
d.text((100, 570), "Sumber isi: JSONPlaceholder · https://jsonplaceholder.typicode.com/todos/1", font=FS, fill="#555")
d.text((100, 610), "Kolase UI: Koding Indonesia (FS-30) — bukan screenshot resmi Chrome", font=FS, fill="#555")
br.save(OUT / "fs30-browser-json.png", optimize=True)
print("browser", (OUT / "fs30-browser-json.png").stat().st_size)
print("done")
