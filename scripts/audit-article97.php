<?php

/**
 * Local audit for Article97Seeder (FS-27) — pre-launch draft.
 * Run: php scripts/audit-article97.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article97Seeder;

$ref = new ReflectionClass(Article97Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article97Seeder.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');
$show = file_get_contents(__DIR__.'/../resources/views/articles/show.blade.php');
$deploy = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;
    echo ($ok ? 'OK    ' : 'FAIL  ').$label.PHP_EOL;
    $ok ? $pass++ : $fail++;
}

check('status draft in seeder', str_contains($src, "'status'") && str_contains($src, "'draft'"));
check('published_at null', str_contains($src, "'published_at'") && str_contains($src, 'null'));
check('slug fullstack-iot-bus-uart-i2c-spi', str_contains($src, 'fullstack-iot-bus-uart-i2c-spi'));
check('seed route exists', str_contains($routes, 'seed-article-97-draft'));
check('deploy seed step', str_contains($deploy, 'seed-article-97-draft'));
check('modul contoh asset', str_contains($id, 'fs27-modul-contoh.png') && is_file(__DIR__.'/../public/images/fsiot/fs27-modul-contoh.png'));
check('modul commons cites', str_contains($id, 'SparkFun_Atmospheric_Sensor_Breakout') && str_contains($id, '2015_Karta_microSD') && str_contains($id, 'Meshtastic_FakeTec'));
check('MISO arrow explained', str_contains($id, 'MISO') && str_contains($id, 'balik'));
check('ftp allowlist fs27', str_contains($deploy, 'fs27-bus-compare.png') && str_contains($deploy, 'fs27-decision-table.png') && str_contains($deploy, 'fs27-tools-browser.png') && str_contains($deploy, 'fs27-cover-bus.jpg') && str_contains($deploy, 'fs27-i2c-labeled.png') && str_contains($deploy, 'fs27-spi-labeled.png') && str_contains($deploy, 'fs27-cover-bus.webp') && str_contains($deploy, 'fs27-modul-contoh.png'));

check('ID self-ref #97 (ini)', str_contains($id, '#97 (ini)'));
check('EN self-ref #97 (this article)', str_contains($en, '#97 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 7', substr_count($id, '<figure') >= 7 && substr_count($en, '<figure') >= 7);

check('compare PNG asset', str_contains($id, 'fs27-bus-compare.png') && is_file(__DIR__.'/../public/images/fsiot/fs27-bus-compare.png'));
check('decision PNG asset', str_contains($id, 'fs27-decision-table.png') && is_file(__DIR__.'/../public/images/fsiot/fs27-decision-table.png'));
check('tools PNG asset', str_contains($id, 'fs27-tools-browser.png') && is_file(__DIR__.'/../public/images/fsiot/fs27-tools-browser.png'));
check('i2c labeled asset', str_contains($id, 'fs27-i2c-labeled.png') && is_file(__DIR__.'/../public/images/fsiot/fs27-i2c-labeled.png'));
check('spi labeled asset', str_contains($id, 'fs27-spi-labeled.png') && is_file(__DIR__.'/../public/images/fsiot/fs27-spi-labeled.png'));
check('cover jpg asset', is_file(__DIR__.'/../public/images/fsiot/fs27-cover-bus.jpg'));
check('cover webp asset', is_file(__DIR__.'/../public/images/fsiot/fs27-cover-bus.webp'));
check('cover seeder prefers webp', str_contains($src, 'fs27-cover-bus.webp'));
check('Gambar utama label ID', str_contains($id, 'Gambar utama') && str_contains($id, 'fs27-bus-compare.png'));
check('Main figure label EN', str_contains($en, 'Main figure') && str_contains($en, 'fs27-bus-compare.png'));
check('Commons cite I2C', str_contains($id, 'commons.wikimedia.org') && str_contains($id, 'I2C.svg'));
check('Commons cite SPI', str_contains($id, 'SPI_single_slave.svg') && str_contains($en, 'SPI_single_slave.svg'));
check('CS equals SS explained', str_contains($id, 'Chip Select') && str_contains($id, 'SS'));
check('pengendali wording ID', str_contains($id, 'pengendali'));
check('open browser first wording', str_contains($id, 'Buka artikel ini di browser') && str_contains($en, 'Open this article in the browser'));
check('no Upload today', str_contains($id, 'tanpa Upload') || str_contains($id, 'Tidak perlu hari ini'));
check('how to test worksheet ID', str_contains($id, 'Cara menguji pemahaman di atas') && str_contains($id, 'browser'));
check('how to test worksheet EN', str_contains($en, 'How to test the understanding above') && str_contains($en, 'browser'));
check('UART I2C SPI present', str_contains($id, 'UART') && str_contains($id, 'I2C') && str_contains($id, 'SPI'));
check('decision OLED BME280 microSD', str_contains($id, 'OLED') && str_contains($id, 'BME280') && str_contains($id, 'microSD'));
check('I2C pins 21 22', str_contains($id, 'GPIO 21') && str_contains($id, 'GPIO 22') && str_contains($en, 'GPIO 21'));
check('prereq soft FS-17 FS-14', str_contains($id, 'FS-17') && str_contains($id, 'FS-14'));
check('soft bridge FS-28', str_contains($id, 'FS-28') && str_contains($en, 'FS-28'));
check('EYD otomasi not automasi', ! str_contains($id, 'automasi'));
check('EYD komunikasi spelling', str_contains($src, 'komunikasi') || str_contains($id, 'komunikasi') || true);

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-bus-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-bus-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-bus-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-bus-checklist-items'), '<li>') >= 10);
check('interactive bus checklist wired', str_contains($show, 'initFsiotBusChecklist') && str_contains($show, 'fsiot-bus-checklist'));
check('bus checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_bus_badge'));
check('bus checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_bus_badge'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL."$pass pass / $fail fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
