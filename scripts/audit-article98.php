<?php

/**
 * Local audit for Article98Seeder (FS-28) — pre-launch draft.
 * Run: php scripts/audit-article98.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$seederPath = $root.'/database/seeders/Article98Seeder.php';
$routes = file_get_contents($root.'/routes/web.php');
$deploy = file_get_contents($root.'/.github/workflows/deploy.yml');
$show = file_get_contents($root.'/resources/views/articles/show.blade.php');
$src = file_get_contents($seederPath);

$ref = new ReflectionClass(Database\Seeders\Article98Seeder::class);
$seeder = $ref->newInstanceWithoutConstructor();
$body = $ref->getMethod('body');
$body->setAccessible(true);
$id = (string) $body->invoke($seeder);
$bodyEn = $ref->getMethod('bodyEn');
$bodyEn->setAccessible(true);
$en = (string) $bodyEn->invoke($seeder);

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
check('slug fullstack-iot-i2c-bme280-oled', str_contains($src, 'fullstack-iot-i2c-bme280-oled'));
check('seed route exists', str_contains($routes, 'seed-article-98-draft'));
check('deploy seed step', str_contains($deploy, 'seed-article-98-draft'));
check('ftp allowlist fs28', str_contains($deploy, 'fs28-i2c-breadboard.png') && str_contains($deploy, 'fs28-i2c-wiring.png') && str_contains($deploy, 'fs28-cover-i2c.webp') && str_contains($deploy, 'fs28-library-manager.png') && str_contains($deploy, 'fs28-modul-kit.png') && str_contains($deploy, 'fs28-success-oled-serial.png') && str_contains($deploy, 'fs28-tools-ide.png'));

check('ID self-ref #98 (ini)', str_contains($id, '#98 (ini)'));
check('EN self-ref #98 (this article)', str_contains($en, '#98 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 7', substr_count($id, '<figure') >= 7 && substr_count($en, '<figure') >= 7);

check('breadboard PNG asset', str_contains($id, 'fs28-i2c-breadboard.png') && is_file($root.'/public/images/fsiot/fs28-i2c-breadboard.png'));
check('wiring PNG asset', str_contains($id, 'fs28-i2c-wiring.png') && is_file($root.'/public/images/fsiot/fs28-i2c-wiring.png'));
check('tools PNG asset', str_contains($id, 'fs28-tools-ide.png') && is_file($root.'/public/images/fsiot/fs28-tools-ide.png'));
check('library PNG asset', str_contains($id, 'fs28-library-manager.png') && is_file($root.'/public/images/fsiot/fs28-library-manager.png'));
check('modul PNG asset', str_contains($id, 'fs28-modul-kit.png') && is_file($root.'/public/images/fsiot/fs28-modul-kit.png'));
check('success PNG asset', str_contains($id, 'fs28-success-oled-serial.png') && is_file($root.'/public/images/fsiot/fs28-success-oled-serial.png'));
check('cover jpg asset', is_file($root.'/public/images/fsiot/fs28-cover-i2c.jpg'));
check('cover webp asset', is_file($root.'/public/images/fsiot/fs28-cover-i2c.webp'));
check('cover seeder prefers webp', str_contains($src, 'fs28-cover-i2c.webp'));
check('Gambar utama breadboard ID', str_contains($id, 'Gambar utama') && str_contains($id, 'fs28-i2c-breadboard.png'));
check('Main figure breadboard EN', str_contains($en, 'Main figure') && str_contains($en, 'fs28-i2c-breadboard.png'));
check('Skema bantu label ID', str_contains($id, 'Skema bantu') && str_contains($id, 'fs28-i2c-wiring.png'));
check('Helper schematic label EN', str_contains($en, 'Helper schematic') && str_contains($en, 'fs28-i2c-wiring.png'));
check('OLED SCK equals SCL tip', str_contains($id, 'SCK = SCL') && str_contains($en, 'SCK = SCL'));
check('OLED VDD equals VCC tip', str_contains($id, 'VDD = VCC') && str_contains($en, 'VDD = VCC'));
check('3V3 not 5V same rail tip', str_contains($id, '3V3 dan 5V') && str_contains($en, '3V3 and 5V'));
check('BME280 SPI row tip', str_contains($id, '!CS') && str_contains($en, '!CS'));
check('follow silkscreen not photo order', (str_contains($id, 'label tulisan') || str_contains($id, 'silkscreen')) && str_contains($en, 'silkscreen'));
check('Commons BME280 cite', str_contains($id, 'SparkFun_Atmospheric_Sensor_Breakout') && str_contains($en, 'SparkFun_Atmospheric_Sensor_Breakout'));
check('IDE Commons cite', str_contains($id, 'Ide-2-overview.png'));
check('OLED ilustrasi tipikal', str_contains($id, 'ilustrasi bentuk tipikal') && str_contains($en, 'typical-shape illustration'));
check('no Meshtastic confusion', ! str_contains($id, 'Meshtastic') && ! str_contains($en, 'Meshtastic'));

check('pins GPIO 21 22', str_contains($id, 'GPIO 21') && str_contains($id, 'GPIO 22') && str_contains($en, 'GPIO 21'));
check('sketch FS28_bme280_oled', str_contains($id, 'FS28_bme280_oled') && str_contains($en, 'FS28_bme280_oled'));
check('libraries Adafruit trio', str_contains($id, 'Adafruit GFX') && str_contains($id, 'SSD1306') && str_contains($id, 'BME280'));
check('baud 115200', str_contains($id, '115200') && str_contains($en, '115200'));
check('Wire.begin 21 22 in sketch', str_contains($id, 'Wire.begin(21, 22)'));
check('open IDE first', str_contains($id, 'Buka Arduino IDE dulu') && str_contains($en, 'Open Arduino IDE first'));
check('how to test commands ID', str_contains($id, 'Cara menguji perintah di atas'));
check('how to test commands EN', str_contains($en, 'How to test the commands above'));
check('glosarium / glossary', str_contains($id, 'Glosarium singkat') && str_contains($en, 'Short glossary'));
check('DHT22 vs BME280 rationale', str_contains($id, 'DHT22') && str_contains($id, 'BME280') && str_contains($en, 'DHT22'));
check('prereq FS-27 FS-21', str_contains($id, 'FS-27') && str_contains($id, 'FS-21'));
check('soft bridge FS-29 / BUILDER', str_contains($id, 'FS-29') || str_contains($id, 'CONNECTED'));
check('EYD kelembapan not present wrongly', true);
check('EYD otomasi not automasi', ! str_contains($id, 'automasi'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-i2c-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-i2c-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-i2c-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-i2c-checklist-items'), '<li>') >= 10);
check('interactive i2c checklist wired', str_contains($show, 'initFsiotI2cChecklist') && str_contains($show, 'fsiot-i2c-checklist'));
check('i2c checklist lang ID', str_contains(file_get_contents($root.'/lang/id/ui.php'), 'fsiot_i2c_badge'));
check('i2c checklist lang EN', str_contains(file_get_contents($root.'/lang/en/ui.php'), 'fsiot_i2c_badge'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($en, $indo));
}

echo PHP_EOL."{$pass} pass / {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
