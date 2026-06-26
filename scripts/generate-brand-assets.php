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
    fwrite(STDERR, "Python OG generation failed. Install Pillow and run:\n");
    fwrite(STDERR, "  python scripts/generate-og-image.py public/logo_fill.png public/og-default.png\n");
    if (! empty($pyOut)) {
        fwrite(STDERR, implode("\n", $pyOut) . "\n");
    }
    imagedestroy($logo);
    exit(1);
}

imagedestroy($logo);

echo "Done.\n";
