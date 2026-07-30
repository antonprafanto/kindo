<?php

/**
 * Local audit for Article75Seeder (FS-05) — pre-launch draft.
 * Run: php scripts/audit-article75.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article75Seeder;

$ref = new ReflectionClass(Article75Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article75Seeder.php');
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
check('slug fullstack-iot-keselamatan-sebelum-listrik', str_contains($src, 'fullstack-iot-keselamatan-sebelum-listrik'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains($routes, 'publish-article-75'));
check('seed route exists', str_contains($routes, 'seed-article-75-draft'));

check('ID self-ref #75 (ini)', str_contains($id, '#75 (ini)'));
check('EN self-ref #75 (this article)', str_contains($en, '#75 (this article)'));
check('ID Awam >= 5', substr_count($id, 'Awam:') + substr_count($id, 'Awam —') >= 5);
check('EN Beginner >= 5', substr_count($en, 'Beginner:') + substr_count($en, 'Beginner —') >= 5);
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 4', substr_count($id, '<figure') >= 4 && substr_count($en, '<figure') >= 4);

check('ID Persiapan + no-syntax', str_contains($id, 'Persiapan') && str_contains($id, 'Tidak ada perintah sintaks hari ini'));
check('EN Preparation + no-syntax', str_contains($en, 'Preparation') && str_contains($en, 'There is no syntax to run today'));
check('ID tools-first browser checklist', str_contains($id, 'Browser') && str_contains($id, 'checklist interaktif'));
check('EN tools-first browser checklist', str_contains($en, 'Browser') && str_contains($en, 'interactive checklist'));

check('short circuit both', str_contains($id, 'Short circuit') && (str_contains($en, 'short circuit') || str_contains($en, 'Short circuit')));
check('3.3V vs 5V both', str_contains($id, '3.3V') && str_contains($id, '5V') && str_contains($en, '3.3V') && str_contains($en, '5V'));
check('unplug USB habit', str_contains($id, 'cabut USB') && str_contains($en, 'unplug USB'));
check('hot-plug mentioned', str_contains($id, 'Hot-plug') || str_contains($id, 'hot-plug'));
check('charge-only both', str_contains($id, 'charge-only') && str_contains($en, 'charge-only'));
check('no GPIO 5V encouragement', str_contains($id, 'jangan') && str_contains($id, 'GPIO'));
check('overview image + Espressif', str_contains($id, 'esp32-devkitc-overview.jpg') && str_contains($id, 'Espressif'));
check('multimeter image + Commons', str_contains($id, 'kit-multimeter.jpg') && str_contains($id, 'commons.wikimedia.org'));
check('SVG short + voltage + unplug', str_contains($id, 'Short circuit') && str_contains($id, '3.3V vs 5V') && str_contains($id, 'cabut USB dulu'));

check('checklist markers', str_contains($id, 'id="fsiot-safety-checklist"') && str_contains($id, 'id="fsiot-safety-checklist-items"'));
check('EN checklist markers', str_contains($en, 'id="fsiot-safety-checklist"') && str_contains($en, 'id="fsiot-safety-checklist-items"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-safety-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-safety-checklist-items'), '<li>') >= 10);

check('soft mention FS-04', str_contains($id, 'FS-04') && str_contains($en, 'FS-04'));
check('soft bridge FS-06', str_contains($id, 'FS-06') && str_contains($en, 'FS-06'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no Seri ESP32 prereq link', ! preg_match('#/artikel/(esp32|arduino)#i', $id.$en));
check('no pre blocks', ! str_contains($id, '<pre') && ! str_contains($en, '<pre'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('belum power ON practice', str_contains($id, 'belum colok USB') || str_contains($id, 'Belum colok USB'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan umum', 'Kesimpulan', 'Awam:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL."{$pass} pass / {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
