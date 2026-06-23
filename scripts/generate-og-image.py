from PIL import Image, ImageDraw, ImageFont
import sys

W, H = 1200, 630
logo_path, out_path = sys.argv[1], sys.argv[2]


def load_font(size, bold=True):
    candidates = [
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        'C:/Windows/Fonts/arialbd.ttf',
        'C:/Windows/Fonts/arial.ttf',
    ]
    for path in candidates:
        try:
            return ImageFont.truetype(path, size)
        except OSError:
            continue
    return ImageFont.load_default()


img = Image.new('RGB', (W, H), '#2979FF')
draw = ImageDraw.Draw(img)

for x in range(0, W, 28):
    for y in range(0, H, 28):
        draw.ellipse([x, y, x + 2, y + 2], fill='#ffffff18')

logo = Image.open(logo_path).convert('RGBA')
logo_size = 200
logo = logo.resize((logo_size, logo_size), Image.Resampling.LANCZOS)
lx, ly = 64, (H - logo_size) // 2

# White tile behind filled logo for contrast on blue background
tile_pad = 8
draw.rounded_rectangle(
    [lx - tile_pad, ly - tile_pad, lx + logo_size + tile_pad, ly + logo_size + tile_pad],
    radius=24,
    fill='#FFFFFF',
    outline='#000000',
    width=3,
)
img.paste(logo, (lx, ly), logo)

title_font = load_font(72)
sub_font = load_font(36, bold=False)
tx = 300
draw.text((tx, 180), 'Koding Indonesia', fill='#FFFFFF', font=title_font)
draw.text((tx, 280), 'Tutorial ESP32 & IoT', fill='#E2E8F0', font=sub_font)

img.save(out_path, 'PNG')
