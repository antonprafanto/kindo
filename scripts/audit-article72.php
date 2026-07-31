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
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 4', substr_count($id, '<figure') >= 4 && substr_count($en, '<figure') >= 4);
check('SVG layers diagram', str_contains($id, 'SATU GAMBAR') && str_contains($id, 'viewBox="0 0 760 520"'));
check('layers stacked not side-by-side', ! str_contains($id, 'width="330" height="42"') && str_contains($id, '5. Penyimpanan'));
check('phases ZERO..HERO', str_contains($id, 'ZERO') && str_contains($id, 'BUILDER') && str_contains($id, 'HERO'));
check('EN phases present', str_contains($en, 'ZERO') && str_contains($en, 'CONNECTED') && str_contains($en, 'FULLSTACK'));
check('you are here / kamu di sini', str_contains($id, 'kamu di sini') && str_contains($en, 'you are here'));
check('fondasi dari nol', str_contains($id, 'fondasi dari nol') && str_contains($en, 'foundation from zero'));
check('ID Persiapan + no-syntax', str_contains($id, 'Persiapan') && str_contains($id, 'Tidak ada perintah sintaks hari ini'));
check('EN Preparation + no-syntax', str_contains($en, 'Preparation') && str_contains($en, 'There is no syntax to run today'));
check('ID worksheet', str_contains($id, 'worksheet') || str_contains($id, '________________'));
check('EN worksheet blanks', str_contains($en, '________________'));
check('worksheet interactive markers', str_contains($id, 'id="fsiot-worksheet-boxes"') && str_contains($en, 'id="fsiot-worksheet-boxes"') && str_contains($id, 'id="fsiot-layer-roles"'));
check('ID interactive worksheet mention', str_contains($id, 'worksheet interaktif'));
check('EN interactive worksheet mention', str_contains($en, 'interactive worksheet'));
check('ID tools-first browser worksheet', str_contains($id, 'Browser') && str_contains($id, 'worksheet interaktif'));
check('EN tools-first browser worksheet', str_contains($en, 'Browser') && str_contains($en, 'interactive worksheet'));
check('Stasiun / Study Room', str_contains($id, 'Stasiun Ruang Belajar') && str_contains($en, 'Study Room Station'));
check('DevKitC-1 mention', str_contains($id, 'ESP32-DevKitC-1') && str_contains($en, 'ESP32-DevKitC-1'));
check('soft mention FS-01', str_contains($id, 'FS-01') && str_contains($en, 'FS-01'));
check('soft bridge FS-03', str_contains($id, 'FS-03') && str_contains($en, 'FS-03'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink #71 article', ! str_contains($id, '/artikel/fullstack-iot-apa-itu-iot') && ! str_contains($en, '/artikel/fullstack-iot-apa-itu-iot'));
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no Seri ESP32 prereq link', ! preg_match('#/artikel/(esp32|arduino)#i', $id.$en));
check('no pre/code blocks', ! str_contains($id, '<pre') && ! str_contains($en, '<pre'));
check('ID no Serial jargon early', ! str_contains($id, 'Serial'));
check('ID Indonesian-first device label', str_contains($id, 'Perangkat'));
check('no advanced hero-desk', ! str_contains($id, 'hero-desk.jpg') && ! str_contains($en, 'hero-desk.jpg'));
check('ID real-world lamp photo', str_contains($id, 'fs02-real-world-lamp.jpg') && is_file(__DIR__.'/../public/images/fsiot/fs02-real-world-lamp.jpg'));
check('ID lamp Commons cite', str_contains($id, 'LIFX_bulbs.jpg'));
check('ID board overview photo', str_contains($id, 'esp32-devkitc-overview.jpg') && str_contains($id, 'Espressif Systems'));
check('EN board overview photo', str_contains($en, 'esp32-devkitc-overview.jpg') && str_contains($en, 'Espressif Systems'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('EYD perangkat lunak', str_contains($id, 'perangkat lunak'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL."{$pass} pass / {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
