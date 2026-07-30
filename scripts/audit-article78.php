<?php

/**
 * Local audit for Article78Seeder (FS-08) — pre-launch draft.
 * Run: php scripts/audit-article78.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article78Seeder;

$ref = new ReflectionClass(Article78Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article78Seeder.php');
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
check('slug fullstack-iot-listrik-mini-tegangan-arus-resistansi', str_contains($src, 'fullstack-iot-listrik-mini-tegangan-arus-resistansi'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains($routes, 'publish-article-78'));
check('seed route exists', str_contains($routes, 'seed-article-78-draft'));

check('ID self-ref #78 (ini)', str_contains($id, '#78 (ini)'));
check('EN self-ref #78 (this article)', str_contains($en, '#78 (this article)'));
check('ID Awam >= 5', substr_count($id, 'Awam:') + substr_count($id, 'Awam —') >= 5);
check('EN Beginner >= 5', substr_count($en, 'Beginner:') + substr_count($en, 'Beginner —') >= 5);
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 5', substr_count($id, '<figure') >= 5 && substr_count($en, '<figure') >= 5);

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Kalkulator HP'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Phone calculator'));
check('no breadboard wiring today ID', str_contains($id, 'Tidak ada wiring breadboard'));
check('no breadboard wiring today EN', str_contains($en, 'No breadboard wiring'));
check('water analogy both', str_contains($id, 'Analogi air') && str_contains($en, 'Water analogy'));
check('Ohm formula both', str_contains($id, 'V = I x R') && str_contains($en, 'V = I x R'));
check('220 and 330 both', str_contains($id, '220') && str_contains($id, '330') && str_contains($en, '220') && str_contains($en, '330'));
check('130 ohm result', str_contains($id, '130 ohm') && str_contains($en, '130 ohm'));
check('LED + resistor images', str_contains($id, 'kit-led-5mm.jpg') && str_contains($id, 'kit-resistor.jpg'));
check('SVG water + circuit', str_contains($id, 'Buatan Koding Indonesia') && str_contains($id, '3V3'));
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('calc root marker', str_contains($id, 'id="fsiot-resistor-calc-root"') && str_contains($en, 'id="fsiot-resistor-calc-root"'));
check('checklist markers', str_contains($id, 'id="fsiot-electric-checklist"') && str_contains($id, 'id="fsiot-electric-checklist-items"'));
check('checklist has 8 items ID', substr_count(strstr($id, 'fsiot-electric-checklist-items'), '<li>') >= 8);
check('checklist has 8 items EN', substr_count(strstr($en, 'fsiot-electric-checklist-items'), '<li>') >= 8);

check('soft mention FS-07', str_contains($id, 'FS-07') && str_contains($en, 'FS-07'));
check('soft bridge FS-09', str_contains($id, 'FS-09') && str_contains($en, 'FS-09'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan in practice', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('no Arduino IDE required', str_contains($id, 'belum upload sketch') || str_contains($id, 'belum wiring'));
check('measurement table', str_contains($id, '<table') && str_contains($en, '<table'));

check('No residual Indo in EN: Pendahuluan', ! str_contains($en, 'Pendahuluan'));
check('No residual Indo in EN: Persiapan', ! str_contains($en, 'Persiapan'));
check('No residual Indo in EN: Kesalahan umum', ! str_contains($en, 'Kesalahan umum'));
check('No residual Indo in EN: Kesimpulan', ! str_contains($en, 'Kesimpulan'));
check('No residual Indo in EN: Awam:', ! str_contains($en, 'Awam:'));

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
