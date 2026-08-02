<?php

/**
 * Local audit for Article87Seeder (FS-17) — pre-launch draft.
 * Run: php scripts/audit-article87.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article87Seeder;

$ref = new ReflectionClass(Article87Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article87Seeder.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');
$show = file_get_contents(__DIR__.'/../resources/views/articles/show.blade.php');

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
check('slug fullstack-iot-peta-pin-devkitc-1', str_contains($src, 'fullstack-iot-peta-pin-devkitc-1'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner for'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-87'));
check('seed route exists', str_contains($routes, 'seed-article-87-draft'));

check('ID self-ref #87 (ini)', str_contains($id, '#87 (ini)'));
check('EN self-ref #87 (this article)', str_contains($en, '#87 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 6', substr_count($id, '<figure') >= 6 && substr_count($en, '<figure') >= 6);
check('starter three labels ID', str_contains($id, 'Mulai dari 3 label') || str_contains($id, 'tiga label'));
check('starter three labels EN', str_contains($en, 'Start with only 3 labels') || str_contains($en, 'three starter'));
check('GPIO2 not dual-listed as aman contoh 2,', ! preg_match('/contoh: 2,/', $id));
check('proses menyala boot wording', str_contains($id, 'proses menyala') && str_contains($en, 'power-on'));

check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('tools-first no PHP today', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'browser'));

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Buka') && str_contains($id, 'browser'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Open') && str_contains($en, 'browser'));
check('how to test no Upload ID', str_contains($id, 'Cara menguji pemahaman di atas') && str_contains($id, 'tanpa Upload'));
check('how to test no Upload EN', str_contains($en, 'How to test the understanding above') && str_contains($en, 'no Upload'));

check('pinlayout asset', str_contains($id, 'esp32-devkitc-1-pinlayout.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-1-pinlayout.jpg'));
check('board overview reused', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('Espressif DevKitC-1 cite', str_contains($id, 'boards/ESP32-DevKitC-1.html') && str_contains($en, 'boards/ESP32-DevKitC-1.html'));
check('Espressif user guide cite', str_contains($id, 'esp32-devkitc/user_guide.html') && str_contains($en, 'esp32-devkitc/user_guide.html'));
check('KI diagram cite', str_contains($id, 'buatan Koding Indonesia') && str_contains($en, 'diagram by Koding Indonesia'));
check('forbidden IO6-IO11', str_contains($id, 'IO6') && str_contains($id, 'IO11') && str_contains($en, 'IO6') && str_contains($en, 'IO11'));
check('input-only pins', str_contains($id, '34') && str_contains($id, '39') && str_contains($en, 'input-only'));
check('global LED GPIO 2', str_contains($id, 'GPIO 2') || str_contains($id, '<strong>2</strong>'));
check('global button GPIO 27', str_contains($id, '27'));
check('BUILDER phase mention', str_contains($id, 'BUILDER') && str_contains($en, 'BUILDER'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-pin-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-pin-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-pin-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-pin-checklist-items'), '<li>') >= 10);
check('interactive pin checklist wired', str_contains($show, 'initFsiotPinChecklist') && str_contains($show, 'fsiot-pin-checklist'));
check('pin checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_pin_badge'));
check('pin checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_pin_badge'));

check('soft mention FS-16', str_contains($id, 'FS-16') && str_contains($en, 'FS-16'));
check('soft bridge FS-18', str_contains($id, 'FS-18') && str_contains($en, 'FS-18'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan serve', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('pin table present', str_contains($id, '<table>') && str_contains($en, '<table>'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
