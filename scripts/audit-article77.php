<?php

/**
 * Local audit for Article77Seeder (FS-07) — pre-launch draft.
 * Run: php scripts/audit-article77.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article77Seeder;

$ref = new ReflectionClass(Article77Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article77Seeder.php');
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
check('slug fullstack-iot-multimeter-untuk-awam', str_contains($src, 'fullstack-iot-multimeter-untuk-awam'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains($routes, 'publish-article-77'));
check('seed route exists', str_contains($routes, 'seed-article-77-draft'));

check('ID self-ref #77 (ini)', str_contains($id, '#77 (ini)'));
check('EN self-ref #77 (this article)', str_contains($en, '#77 (this article)'));
check('ID Awam >= 5', substr_count($id, 'Awam:') + substr_count($id, 'Awam —') >= 5);
check('EN Beginner >= 5', substr_count($en, 'Beginner:') + substr_count($en, 'Beginner —') >= 5);
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 6', substr_count($id, '<figure') >= 6 && substr_count($en, '<figure') >= 6);

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Multimeter digital'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Digital multimeter'));
check('no Arduino syntax today ID', str_contains($id, 'Tidak ada perintah Arduino'));
check('no Arduino syntax today EN', str_contains($en, 'No Arduino commands'));
check('V DC mode both', str_contains($id, 'V DC') && str_contains($en, 'V DC'));
check('continuity both', str_contains($id, 'continuity') && str_contains($en, 'continuity'));
check('3V3 measure both', str_contains($id, '3V3') && str_contains($en, '3V3'));
check('5V measure both', str_contains($id, '5V') && str_contains($en, '5V'));
check('measurement table', str_contains($id, '<table') && str_contains($en, '<table'));
check('multimeter image + Commons', str_contains($id, 'kit-multimeter.jpg') && str_contains($id, 'commons.wikimedia.org'));
check('pinout image + Espressif', str_contains($id, 'esp32-devkitc-1-pinlayout.jpg') && str_contains($id, 'ESP32-DevKitC-1.html'));
check('SVG workflow + dial + probe + continuity', str_contains($id, 'Alur hari ini') && str_contains($id, 'stroke-dasharray') && str_contains($id, 'Probe — colok') && str_contains($id, 'Tes jumper'));
check('no broken V glyph', ! str_contains($id, 'V⎓') && ! str_contains($en, 'V⎓'));
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));
check('no ampere mode encouragement', str_contains($id, 'JANGAN') && str_contains($en, 'NOT today'));

check('checklist markers', str_contains($id, 'id="fsiot-multimeter-checklist"') && str_contains($id, 'id="fsiot-multimeter-checklist-items"'));
check('EN checklist markers', str_contains($en, 'id="fsiot-multimeter-checklist"') && str_contains($en, 'id="fsiot-multimeter-checklist-items"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-multimeter-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-multimeter-checklist-items'), '<li>') >= 10);

check('soft mention FS-06', str_contains($id, 'FS-06') && str_contains($en, 'FS-06'));
check('soft bridge FS-08', str_contains($id, 'FS-08') && str_contains($en, 'FS-08'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no Seri ESP32 prereq link', ! preg_match('#/artikel/(esp32|arduino)#i', $id.$en));
check('no php artisan in practice', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('no Laragon as main tool', ! str_contains($id, 'Laragon') || str_contains($id, 'belum Laragon'));
check('unplug for continuity', str_contains($id, 'Cabut USB') && str_contains($en, 'Unplug USB'));

check('No residual Indo in EN: Pendahuluan', ! str_contains($en, 'Pendahuluan'));
check('No residual Indo in EN: Persiapan', ! str_contains($en, 'Persiapan'));
check('No residual Indo in EN: Kesalahan umum', ! str_contains($en, 'Kesalahan umum'));
check('No residual Indo in EN: Kesimpulan', ! str_contains($en, 'Kesimpulan'));
check('No residual Indo in EN: Awam:', ! str_contains($en, 'Awam:'));

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
