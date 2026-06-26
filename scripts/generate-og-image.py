"""
Generate og-default.png (1200x630) with headline + CTA for social share previews.

Layout: white content panel (left) + blue brand panel with logo (right).
"""

from __future__ import annotations

import sys
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

W, H = 1200, 630
SPLIT = 700  # white panel width

BLUE = "#2979FF"
ORANGE = "#FF7A2F"
INK = "#0F172A"
MUTED = "#475569"
WHITE = "#FFFFFF"
DOT = "#FFFFFF18"

BADGE = "Gratis untuk Pemula"
HEADLINE = "Belajar ESP32 & IoT"
DESCRIPTION = (
    "Tutorial praktis berbahasa Indonesia — dari blink LED hingga proyek MQTT."
)
DETAILS = "Bahasa Indonesia • Tutorial step-by-step"
CTA = "Mulai Belajar →"
DOMAIN = "KODINGINDONESIA.COM"


def load_font(size: int, bold: bool = True) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    regular = [
        "C:/Windows/Fonts/segoeui.ttf",
        "C:/Windows/Fonts/arial.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
    ]
    bold_paths = [
        "C:/Windows/Fonts/segoeuib.ttf",
        "C:/Windows/Fonts/arialbd.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf",
    ]
    candidates = bold_paths if bold else regular
    for path in candidates:
        try:
            return ImageFont.truetype(path, size)
        except OSError:
            continue
    return ImageFont.load_default()


def draw_dot_grid(draw: ImageDraw.ImageDraw, x0: int, y0: int, width: int, height: int, step: int = 28) -> None:
    for x in range(x0, x0 + width, step):
        for y in range(y0, y0 + height, step):
            draw.ellipse([x, y, x + 2, y + 2], fill=DOT)


def text_width(font: ImageFont.ImageFont, text: str) -> int:
    if hasattr(font, "getlength"):
        return int(font.getlength(text))
    return font.getsize(text)[0]


def wrap_text(text: str, font: ImageFont.ImageFont, max_width: int) -> list[str]:
    words = text.split()
    lines: list[str] = []
    current = ""
    for word in words:
        candidate = f"{current} {word}".strip()
        if text_width(font, candidate) <= max_width:
            current = candidate
        else:
            if current:
                lines.append(current)
            current = word
    if current:
        lines.append(current)
    return lines or [text]


def draw_rounded_rect(
    draw: ImageDraw.ImageDraw,
    box: tuple[int, int, int, int],
    radius: int,
    fill: str,
    outline: str | None = None,
    width: int = 0,
) -> None:
    draw.rounded_rectangle(box, radius=radius, fill=fill, outline=outline, width=width)


def main() -> None:
    logo_path = Path(sys.argv[1])
    out_path = Path(sys.argv[2])

    img = Image.new("RGB", (W, H), WHITE)
    draw = ImageDraw.Draw(img)

    # Right brand panel
    draw.rectangle([SPLIT, 0, W, H], fill=BLUE)
    draw_dot_grid(draw, SPLIT, 0, W - SPLIT, H)

    # Left content
    pad_x = 56
    y = 52

    domain_font = load_font(18, bold=True)
    draw.text((pad_x, y), DOMAIN, fill=MUTED, font=domain_font)
    y += 42

    badge_font = load_font(22, bold=True)
    badge_pad_x, badge_pad_y = 18, 10
    badge_text_w = text_width(badge_font, BADGE)
    badge_w = badge_text_w + badge_pad_x * 2
    badge_h = 40
    draw_rounded_rect(draw, (pad_x, y, pad_x + badge_w, y + badge_h), 20, ORANGE)
    draw.text((pad_x + badge_pad_x, y + badge_pad_y - 2), BADGE, fill=WHITE, font=badge_font)
    y += badge_h + 28

    headline_font = load_font(58, bold=True)
    draw.text((pad_x, y), HEADLINE, fill=INK, font=headline_font)
    y += 78

    body_font = load_font(28, bold=False)
    max_text_w = SPLIT - pad_x - 40
    for line in wrap_text(DESCRIPTION, body_font, max_text_w):
        draw.text((pad_x, y), line, fill=MUTED, font=body_font)
        y += 38
    y += 8

    detail_font = load_font(22, bold=True)
    draw.text((pad_x, y), DETAILS, fill=INK, font=detail_font)
    y += 46

    cta_font = load_font(26, bold=True)
    cta_pad_x, cta_pad_y = 28, 14
    cta_text_w = text_width(cta_font, CTA)
    cta_w = cta_text_w + cta_pad_x * 2
    cta_h = 54
    draw_rounded_rect(
        draw,
        (pad_x, y, pad_x + cta_w, y + cta_h),
        10,
        BLUE,
        outline=INK,
        width=3,
    )
    draw.text((pad_x + cta_pad_x, y + cta_pad_y - 2), CTA, fill=WHITE, font=cta_font)

    # Logo card on blue panel
    logo = Image.open(logo_path).convert("RGBA")
    logo_size = 220
    logo = logo.resize((logo_size, logo_size), Image.Resampling.LANCZOS)
    card_pad = 12
    card_w = logo_size + card_pad * 2
    card_h = logo_size + card_pad * 2 + 72
    card_x = SPLIT + ((W - SPLIT) - card_w) // 2
    card_y = (H - card_h) // 2

    draw_rounded_rect(
        draw,
        (card_x, card_y, card_x + card_w, card_y + card_h),
        28,
        WHITE,
        outline=INK,
        width=3,
    )
    img.paste(logo, (card_x + card_pad, card_y + card_pad), logo)

    card_title_font = load_font(24, bold=True)
    card_sub_font = load_font(18, bold=False)
    card_title = "Koding Indonesia"
    card_sub = "Tutorial ESP32 & IoT"
    title_x = card_x + (card_w - text_width(card_title_font, card_title)) // 2
    sub_x = card_x + (card_w - text_width(card_sub_font, card_sub)) // 2
    text_y = card_y + card_pad + logo_size + 14
    draw.text((title_x, text_y), card_title, fill=INK, font=card_title_font)
    draw.text((sub_x, text_y + 30), card_sub, fill=MUTED, font=card_sub_font)

    out_path.parent.mkdir(parents=True, exist_ok=True)
    img.save(out_path, "PNG", optimize=True)
    print(f"Wrote {out_path}")


if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python generate-og-image.py <logo.png> <output.png>", file=sys.stderr)
        sys.exit(1)
    main()
