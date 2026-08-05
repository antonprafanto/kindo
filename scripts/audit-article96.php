<?php

/**
 * Local audit for Article96Seeder (FS-26) — pre-launch draft.
 * Run: php scripts/audit-article96.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article96Seeder;

$ref = new ReflectionClass(Article96Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article96Seeder.php');
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
check('slug fullstack-iot-servo-pwm', str_contains($src, 'fullstack-iot-servo-pwm'));
check('seed route exists', str_contains($routes, 'seed-article-96-draft'));
check('deploy seed step', str_contains($deploy, 'seed-article-96-draft'));
check('ftp allowlist fs26', str_contains($deploy, 'fs26-servo-wiring.png') && str_contains($deploy, 'fs26-servo-breadboard.png') && str_contains($deploy, 'kit-servo-sg90.jpg') && str_contains($deploy, 'fs26-cover-servo.jpg') && str_contains($deploy, 'fs26-library-manager.png') && str_contains($deploy, 'fs26-servo-timing.png'));

check('ID self-ref #96 (ini)', str_contains($id, '#96 (ini)'));
check('EN self-ref #96 (this article)', str_contains($en, '#96 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 7', substr_count($id, '<figure') >= 7 && substr_count($en, '<figure') >= 7);

check('breadboard PNG asset', str_contains($id, 'fs26-servo-breadboard.png') && is_file(__DIR__.'/../public/images/fsiot/fs26-servo-breadboard.png'));
check('wiring PNG asset', str_contains($id, 'fs26-servo-wiring.png') && is_file(__DIR__.'/../public/images/fsiot/fs26-servo-wiring.png'));
check('library manager PNG asset', str_contains($id, 'fs26-library-manager.png') && is_file(__DIR__.'/../public/images/fsiot/fs26-library-manager.png'));
check('timing PNG asset', str_contains($id, 'fs26-servo-timing.png') && is_file(__DIR__.'/../public/images/fsiot/fs26-servo-timing.png'));
check('kit servo asset', str_contains($id, 'kit-servo-sg90.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-servo-sg90.jpg'));
check('cover asset', is_file(__DIR__.'/../public/images/fsiot/fs26-cover-servo.jpg'));
check('Gambar utama label ID', str_contains($id, 'Gambar utama') && str_contains($id, 'fs26-servo-breadboard.png'));
check('Skema bantu label ID', str_contains($id, 'Skema bantu') && str_contains($id, 'fs26-servo-wiring.png'));
check('Main figure label EN', str_contains($en, 'Main figure') && str_contains($en, 'fs26-servo-breadboard.png'));
check('Helper schematic label EN', str_contains($en, 'Helper schematic') && str_contains($en, 'fs26-servo-wiring.png'));
check('Commons cite SG90', str_contains($id, 'commons.wikimedia.org') && str_contains($id, 'Tower_Pro_SG90'));
check('Library Manager docs cite', str_contains($id, 'ide-v2-installing-a-library') && str_contains($en, 'ide-v2-installing-a-library'));
check('EYD sinyal / mikro servo in kit caption ID', str_contains($id, 'mikro servo') && str_contains($id, 'oranye/kuning = sinyal') && ! str_contains($id, 'micro servo <strong>SG90</strong>') && ! str_contains($id, 'kuning/oranye: <strong>Signal</strong>'));
check('IDE caption warns AnalogReadSerial', str_contains($id, 'AnalogReadSerial') && str_contains($en, 'AnalogReadSerial'));
check('Serial panel SVG 115200', str_contains($id, 'Baud: 115200'));
check('open IDE first wording', str_contains($id, 'Buka Arduino IDE dulu') && str_contains($en, 'Open Arduino IDE first'));
check('Library Manager ESP32Servo', str_contains($id, 'Library Manager') && str_contains($id, 'ESP32Servo') && str_contains($en, 'ESP32Servo'));
check('Wajib sebelum Verify', str_contains($id, 'Wajib sebelum Verify') && str_contains($en, 'Required before Verify'));
check('sapu explained', str_contains($id, 'bergerak berurutan') && str_contains($en, 'move in sequence'));
check('how to test IDE Upload ID', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload EN', str_contains($en, 'How to test the commands above') && str_contains($en, 'Arduino IDE'));
check('sketch FS26_servo_sudut', str_contains($id, 'FS26_servo_sudut') && str_contains($en, 'FS26_servo_sudut'));
check('GPIO 13 servo', str_contains($id, 'GPIO 13') && str_contains($en, 'GPIO 13'));
check('SG90', str_contains($id, 'SG90') && str_contains($en, 'SG90'));
check('5V not 3V3 warning', str_contains($id, 'jangan</strong> 3V3') || str_contains($id, 'jangan 3V3') || (str_contains($id, '3V3') && str_contains($id, '5V')));
check('prereq soft FS-20 FS-14', str_contains($id, 'FS-20') && str_contains($id, 'FS-14'));
check('soft bridge FS-27', str_contains($id, 'FS-27') && str_contains($en, 'FS-27'));
check('EYD otomasi not automasi', ! str_contains($id, 'automasi'));
check('checklist soft FS-27 wording', str_contains($id, 'perbandingan bus') && str_contains($en, 'bus comparison'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-servo-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-servo-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-servo-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-servo-checklist-items'), '<li>') >= 10);
check('interactive servo checklist wired', str_contains($show, 'initFsiotServoChecklist') && str_contains($show, 'fsiot-servo-checklist'));
check('servo checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_servo_badge'));
check('servo checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_servo_badge'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL."$pass pass / $fail fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
