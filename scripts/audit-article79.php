<?php

/**
 * Local audit for Article79Seeder (FS-09) — pre-launch draft.
 * Run: php scripts/audit-article79.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article79Seeder;

$ref = new ReflectionClass(Article79Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article79Seeder.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');

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
check('slug fullstack-iot-led-resistor-di-breadboard', str_contains($src, 'fullstack-iot-led-resistor-di-breadboard'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains($routes, 'publish-article-79'));
check('seed route exists', str_contains($routes, 'seed-article-79-draft'));

check('ID self-ref #79 (ini)', str_contains($id, '#79 (ini)'));
check('EN self-ref #79 (this article)', str_contains($en, '#79 (this article)'));
check('ID Awam >= 5', substr_count($id, 'Awam:') + substr_count($id, 'Awam —') >= 5);
check('EN Beginner >= 5', substr_count($en, 'Beginner:') + substr_count($en, 'Beginner —') >= 5);
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 6', substr_count($id, '<figure') >= 6 && substr_count($en, '<figure') >= 6);

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Cabut USB'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Unplug USB'));
check('breadboard wiring today ID', str_contains($id, 'Wiring step-by-step') || str_contains($id, 'wiring'));
check('breadboard wiring today EN', str_contains($en, 'Step-by-step wiring') || str_contains($en, 'wiring'));
check('3V3 pin power ID', str_contains($id, 'pin 3V3') || str_contains($id, 'pin <strong>3V3</strong>'));
check('3V3 pin power EN', str_contains($en, '3V3 pin') || str_contains($en, '<strong>3V3</strong>'));
check('row 12 15 16 both', str_contains($id, 'baris 12') && str_contains($id, 'baris 15') && str_contains($en, 'row 12') && str_contains($en, 'row 15'));
check('kit images', str_contains($id, 'kit-breadboard.jpg') && str_contains($id, 'kit-jumper-wires.jpg') && str_contains($id, 'esp32-devkitc-1-pinlayout.jpg'));
check('SVG wiring + circuit', str_contains($id, 'Buatan Koding Indonesia') && str_contains($id, '220R'));
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-led-circuit-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-led-circuit-checklist"'));
check('checklist markers', str_contains($id, 'id="fsiot-led-circuit-checklist"') && str_contains($id, 'id="fsiot-led-circuit-checklist-items"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-led-circuit-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-led-circuit-checklist-items'), '<li>') >= 10);

check('soft mention FS-08', str_contains($id, 'FS-08') && str_contains($en, 'FS-08'));
check('soft bridge FS-10', str_contains($id, 'FS-10') && str_contains($en, 'FS-10'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan in practice', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('no Arduino IDE upload', str_contains($id, 'belum upload sketch') && str_contains($en, 'no sketch upload'));
check('unplug jumper practice', str_contains($id, 'cabut jumper') && str_contains($en, 'unplug'));

check('No residual Indo in EN: Pendahuluan', ! str_contains($en, 'Pendahuluan'));
check('No residual Indo in EN: Persiapan', ! str_contains($en, 'Persiapan'));
check('No residual Indo in EN: Kesalahan umum', ! str_contains($en, 'Kesalahan umum'));

echo PHP_EOL."Result: {$pass} pass, {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
