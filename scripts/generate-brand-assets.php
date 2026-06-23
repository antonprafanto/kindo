<?php

/**
 * Generate favicons, site logo, and OG default image from source PNGs.
 *
 * Sources (project root):
 *   logo2026.png     → favicons + public/logo.png
 *   logo_fill.png   → OG default image logo tile
 */

$root = dirname(__DIR__);
$public = $root . '/public';
$imagesDir = $public . '/images';

$logoSource = $root . '/logo2026.png';
$ogLogo = $public . '/logo_fill.png';

foreach ([$logoSource, $ogLogo] as $file) {
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

$ogPy = $root . '/scripts/generate-og-image.py';
$tmpOg = $public . '/og-default-tmp.png';
$pyCmd = sprintf(
    'python %s %s %s',
    escapeshellarg($ogPy),
    escapeshellarg($ogLogo),
    escapeshellarg($tmpOg)
);

$pyOk = false;
exec($pyCmd . ' 2>&1', $pyOut, $pyCode);
if ($pyCode === 0 && is_file($tmpOg)) {
    foreach (['og-default.png', 'images/og-default.png'] as $relative) {
        copy($tmpOg, $public . '/' . $relative);
        echo "Wrote {$relative} (clean headline layout)\n";
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

    $ogLogoImg = is_file($ogLogo) ? loadPng($ogLogo) : $logo;
    $logoResized = resizeSquare($ogLogoImg, 200);
    imagecopy($canvas, $logoResized, 64, 215, 0, 0, 200, 200);
    imagerectangle($canvas, 56, 207, 272, 423, $black);

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
        imagettftext($canvas, 48, 0, 300, 220, $white, $font, 'Koding Indonesia');
        imagettftext($canvas, 26, 0, 300, 290, $white, $font, 'Tutorial ESP32 & IoT');
    } else {
        imagestring($canvas, 5, 300, 200, 'Koding Indonesia', $white);
        imagestring($canvas, 4, 300, 270, 'Tutorial ESP32 & IoT', $white);
    }

    foreach (['og-default.png', 'images/og-default.png'] as $relative) {
        savePng($canvas, $public . '/' . $relative);
        echo "Wrote {$relative} (fallback)\n";
    }
    imagedestroy($canvas);
    imagedestroy($logoResized);
    if ($ogLogoImg !== $logo) {
        imagedestroy($ogLogoImg);
    }
}

imagedestroy($logo);

echo "Done.\n";
