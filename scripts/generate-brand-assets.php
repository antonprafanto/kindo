<?php

/**
 * Generate favicons, site logo, and OG default image from source PNGs.
 *
 * Sources (project root):
 *   logo2026.png     → favicons + public/logo.png
 *   Logo Kindo.png   → public/og-default.png (1200×630)
 */

$root = dirname(__DIR__);
$public = $root . '/public';
$imagesDir = $public . '/images';

$logoSource = $root . '/logo2026.png';
$ogSource = $root . '/Logo Kindo.png';

foreach ([$logoSource, $ogSource] as $file) {
    if (! is_file($file)) {
        fwrite(STDERR, "Missing source file: {$file}\n");
        exit(1);
    }
}

if (! is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

function loadPng(string $path): GdImage
{
    $image = imagecreatefrompng($path);
    if ($image === false) {
        throw new RuntimeException("Could not load PNG: {$path}");
    }

    imagealphablending($image, true);
    imagesavealpha($image, true);

    return $image;
}

function resizeSquare(GdImage $source, int $size): GdImage
{
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);

    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefill($canvas, 0, 0, $transparent);

    $width = imagesx($source);
    $height = imagesy($source);
    imagecopyresampled($canvas, $source, 0, 0, 0, 0, $size, $size, $width, $height);

    return $canvas;
}

function savePng(GdImage $image, string $path): void
{
    if (! imagepng($image, $path)) {
        throw new RuntimeException("Could not write PNG: {$path}");
    }
}

$logo = loadPng($logoSource);
copy($logoSource, $public . '/logo.png');

foreach ([16 => 'favicon-16x16.png', 32 => 'favicon-32x32.png', 180 => 'apple-touch-icon.png'] as $size => $filename) {
    $resized = resizeSquare($logo, $size);
    savePng($resized, $public . '/' . $filename);
    imagedestroy($resized);
    echo "Wrote {$filename} ({$size}x{$size})\n";
}

$icoScript = sprintf(
    "from PIL import Image\nimg = Image.open(r'%s')\nimg16 = img.resize((16, 16), Image.Resampling.LANCZOS)\nimg.save(r'%s', format='ICO', sizes=[(16, 16), (32, 32)])\n",
    $public . '/favicon-32x32.png',
    $public . '/favicon.ico'
);
$proc = proc_open('python -', [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
if (is_resource($proc)) {
    fwrite($pipes[0], $icoScript);
    fclose($pipes[0]);
    proc_close($proc);
    echo "Wrote favicon.ico\n";
}

$ogScript = <<<'PY'
from PIL import Image, ImageDraw, ImageFont
import sys

W, H = 1200, 630
logo_path, og_source, out_path = sys.argv[1], sys.argv[2], sys.argv[3]

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

# Subtle grid dots
for x in range(0, W, 28):
    for y in range(0, H, 28):
        draw.ellipse([x, y, x + 2, y + 2], fill='#ffffff18')

# Logo block
logo = Image.open(logo_path).convert('RGBA')
logo = logo.resize((180, 180), Image.Resampling.LANCZOS)
lx, ly = 72, (H - 180) // 2
img.paste(logo, (lx, ly), logo)
draw.rectangle([lx - 4, ly - 4, lx + 184, ly + 184], outline='#000000', width=3)

# Headline + subtitle
title_font = load_font(72)
sub_font = load_font(34, bold=False)
tx = 300
draw.text((tx, 150), 'Koding Indonesia', fill='#FFFFFF', font=title_font)
draw.text((tx, 250), 'Tutorial ESP32 & IoT', fill='#E2E8F0', font=sub_font)
draw.text((tx, 300), 'Berbahasa Indonesia', fill='#E2E8F0', font=sub_font)

# CTA button (neo-brutal)
btn_x1, btn_y1, btn_x2, btn_y2 = tx, 390, tx + 340, 470
draw.rectangle([btn_x1 + 4, btn_y1 + 4, btn_x2 + 4, btn_y2 + 4], fill='#000000')
draw.rectangle([btn_x1, btn_y1, btn_x2, btn_y2], fill='#FF7A2F', outline='#000000', width=3)
cta_font = load_font(30)
draw.text((btn_x1 + 36, btn_y1 + 22), 'Mulai Belajar →', fill='#FFFFFF', font=cta_font)

img.save(out_path, 'PNG')
PY;

$tmpOg = $public . '/og-default-tmp.png';
$pyCmd = sprintf(
    'python -c %s %s %s %s',
    escapeshellarg($ogScript),
    escapeshellarg($public . '/logo.png'),
    escapeshellarg($ogSource),
    escapeshellarg($tmpOg)
);

$pyOk = false;
exec($pyCmd . ' 2>&1', $pyOut, $pyCode);
if ($pyCode === 0 && is_file($tmpOg)) {
    foreach (['og-default.png', 'images/og-default.png'] as $relative) {
        copy($tmpOg, $public . '/' . $relative);
        echo "Wrote {$relative} (with headline + CTA)\n";
    }
    unlink($tmpOg);
    $pyOk = true;
}

if (! $pyOk) {
    fwrite(STDERR, "Python OG generation failed, using fallback layout.\n");
    if (! empty($pyOut)) {
        fwrite(STDERR, implode("\n", $pyOut) . "\n");
    }

    $canvas = imagecreatetruecolor(1200, 630);
    $blue = imagecolorallocate($canvas, 41, 121, 255);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    $orange = imagecolorallocate($canvas, 255, 122, 47);
    $black = imagecolorallocate($canvas, 0, 0, 0);
    imagefill($canvas, 0, 0, $blue);

    $logoResized = resizeSquare($logo, 180);
    imagecopy($canvas, $logoResized, 72, 225, 0, 0, 180, 180);
    imagerectangle($canvas, 68, 221, 256, 409, $black);

    $font = null;
    foreach ([
        'C:/Windows/Fonts/arialbd.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
    ] as $fontPath) {
        if (is_file($fontPath)) {
            $font = $fontPath;
            break;
        }
    }

    if ($font) {
        imagettftext($canvas, 48, 0, 300, 200, $white, $font, 'Koding Indonesia');
        imagettftext($canvas, 26, 0, 300, 270, $white, $font, 'Tutorial ESP32 & IoT');
        imagefilledrectangle($canvas, 304, 400, 640, 460, $orange);
        imagerectangle($canvas, 300, 396, 644, 464, $black);
        imagettftext($canvas, 22, 0, 330, 440, $white, $font, 'Mulai Belajar');
    } else {
        imagestring($canvas, 5, 300, 180, 'Koding Indonesia', $white);
        imagestring($canvas, 4, 300, 250, 'Tutorial ESP32 & IoT', $white);
        imagefilledrectangle($canvas, 300, 390, 640, 450, $orange);
        imagestring($canvas, 4, 330, 410, 'Mulai Belajar', $white);
    }

    foreach (['og-default.png', 'images/og-default.png'] as $relative) {
        savePng($canvas, $public . '/' . $relative);
        echo "Wrote {$relative} (fallback)\n";
    }
    imagedestroy($canvas);
    imagedestroy($logoResized);
}

imagedestroy($logo);

echo "Done.\n";
