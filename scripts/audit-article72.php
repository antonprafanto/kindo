<?php

/**
 * Local audit for Article72Seeder (FS-02) — pre-launch draft.
 * Run: php scripts/audit-article72.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article72Seeder;

$ref = new ReflectionClass(Article72Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article72Seeder.php');

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
check('slug fullstack-iot-satu-gambar-jalur', str_contains($src, 'fullstack-iot-satu-gambar-jalur'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-72'));

check('ID self-ref #72 (ini)', str_contains($id, '#72 (ini)'));
check('EN self-ref #72 (this article)', str_contains($en, '#72 (this article)'));
check('ID Awam >= 5', substr_count($id, 'Awam:') + substr_count($id, 'Awam —') >= 5);
check('EN Beginner >= 5', substr_count($en, 'Beginner:') + substr_count($en, 'Beginner —') >= 5);
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('SVG figures both >= 2', substr_count($id, '<figure') >= 2 && substr_count($en, '<figure') >= 2);
check('SVG layers diagram', str_contains($id, 'tujuh lapisan') || str_contains($id, 'SATU GAMBAR'));
check('phases ZERO..HERO', str_contains($id, 'ZERO') && str_contains($id, 'BUILDER') && str_contains($id, 'HERO'));
check('EN phases present', str_contains($en, 'ZERO') && str_contains($en, 'CONNECTED') && str_contains($en, 'FULLSTACK'));
check('you are here / kamu di sini', str_contains($id, 'kamu di sini') && str_contains($en, 'you are here'));
check('ID Persiapan + no-syntax', str_contains($id, 'Persiapan') && str_contains($id, 'Tidak ada perintah sintaks hari ini'));
check('EN Preparation + no-syntax', str_contains($en, 'Preparation') && str_contains($en, 'There is no syntax to run today'));
check('ID worksheet', str_contains($id, 'worksheet') || str_contains($id, '________________'));
check('EN worksheet blanks', str_contains($en, '________________'));
check('Stasiun / Study Room', str_contains($id, 'Stasiun Ruang Belajar') && str_contains($en, 'Study Room Station'));
check('DevKitC-1 mention', str_contains($id, 'ESP32-DevKitC-1') && str_contains($en, 'ESP32-DevKitC-1'));
check('soft mention FS-01', str_contains($id, 'FS-01') && str_contains($en, 'FS-01'));
check('soft bridge FS-03', str_contains($id, 'FS-03') && str_contains($en, 'FS-03'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink #71 article', ! str_contains($id, '/artikel/fullstack-iot-apa-itu-iot') && ! str_contains($en, '/artikel/fullstack-iot-apa-itu-iot'));
check('no hardlink #73', ! str_contains($id, '/artikel/fullstack-iot') || ! preg_match('#/artikel/fullstack-iot-(?!satu)#', $id.$en));
check('no Seri ESP32 prereq link', ! preg_match('#/artikel/(esp32|arduino)#i', $id.$en));
check('no pre/code blocks', ! str_contains($id, '<pre') && ! str_contains($en, '<pre'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan umum', 'Kesimpulan', 'Awam:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL."{$pass} pass / {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
