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

$kindo = loadPng($ogSource);
$canvas = imagecreatetruecolor(1200, 630);
$black = imagecolorallocate($canvas, 0, 0, 0);
imagefill($canvas, 0, 0, $black);

$sourceWidth = imagesx($kindo);
$sourceHeight = imagesy($kindo);
$maxWidth = 1000;
$maxHeight = 560;
$scale = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
$targetWidth = (int) round($sourceWidth * $scale);
$targetHeight = (int) round($sourceHeight * $scale);
$offsetX = (int) round((1200 - $targetWidth) / 2);
$offsetY = (int) round((630 - $targetHeight) / 2);

imagecopyresampled(
    $canvas,
    $kindo,
    $offsetX,
    $offsetY,
    0,
    0,
    $targetWidth,
    $targetHeight,
    $sourceWidth,
    $sourceHeight
);

foreach (['og-default.png', 'images/og-default.png'] as $relative) {
    savePng($canvas, $public . '/' . $relative);
    echo "Wrote {$relative}\n";
}

imagedestroy($logo);
imagedestroy($kindo);
imagedestroy($canvas);

echo "Done.\n";
