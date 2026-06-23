from PIL import Image, ImageDraw, ImageFont
import os

SRC = os.path.join(os.path.dirname(__file__), "..", "logo2026.png")
PUBLIC = os.path.join(os.path.dirname(__file__), "..", "public")
FAV = os.path.join(PUBLIC, "favicons")
os.makedirs(FAV, exist_ok=True)

logo = Image.open(SRC).convert("RGBA")


def fit_square(size, padding=0.12):
    canvas = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    inner = int(size * (1 - padding * 2))
    copy = logo.copy()
    copy.thumbnail((inner, inner), Image.Resampling.LANCZOS)
    x = (size - copy.width) // 2
    y = (size - copy.height) // 2
    canvas.paste(copy, (x, y), copy)
    return canvas


main = logo.copy()
main.thumbnail((512, 512), Image.Resampling.LANCZOS)
main.save(os.path.join(PUBLIC, "logo.png"), optimize=True)

for size, name in [
    (16, "favicon-16x16.png"),
    (32, "favicon-32x32.png"),
    (180, "apple-touch-icon.png"),
]:
    fit_square(size).save(os.path.join(PUBLIC, name), optimize=True)

for size, name in [(16, "icon-16.png"), (32, "icon-32.png"), (192, "icon-192.png"), (512, "icon-512.png")]:
    fit_square(size).save(os.path.join(FAV, name), optimize=True)

fit_square(256).save(
    os.path.join(PUBLIC, "favicon.ico"),
    format="ICO",
    sizes=[(16, 16), (32, 32), (48, 48), (64, 64)],
)

w, h = 1200, 630
og = Image.new("RGB", (w, h))
draw = ImageDraw.Draw(og)
for y in range(h):
    t = y / h
    r = int(41 + (45 - 41) * t)
    g = int(121 + (55 - 121) * t)
    b = int(255 + (204 - 255) * t)
    draw.line([(0, y), (w, y)], fill=(r, g, b))

og_logo = logo.copy()
og_logo.thumbnail((320, 320), Image.Resampling.LANCZOS)
lx = (w - og_logo.width) // 2
ly = (h - og_logo.height) // 2 - 40
og_rgba = og.convert("RGBA")
og_rgba.paste(og_logo, (lx, ly), og_logo)

try:
    font = ImageFont.truetype("arialbd.ttf", 52)
except OSError:
    font = ImageFont.load_default()

draw2 = ImageDraw.Draw(og_rgba)
text = "Koding Indonesia"
bbox = draw2.textbbox((0, 0), text, font=font)
tx = (w - (bbox[2] - bbox[0])) // 2
ty = ly + og_logo.height + 24
draw2.text((tx + 3, ty + 3), text, fill=(0, 0, 0, 180), font=font)
draw2.text((tx, ty), text, fill=(255, 255, 255, 255), font=font)
og_rgba.convert("RGB").save(os.path.join(PUBLIC, "og-default.png"), optimize=True)

print("Generated brand assets OK")
