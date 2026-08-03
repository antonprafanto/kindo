<?php

/**
 * Local audit for Article88Seeder (FS-18) — pre-launch draft.
 * Run: php scripts/audit-article88.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article88Seeder;

$ref = new ReflectionClass(Article88Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article88Seeder.php');
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
check('slug fullstack-iot-led-dari-kode', str_contains($src, 'fullstack-iot-led-dari-kode'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner for'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-88'));
check('seed route exists', str_contains($routes, 'seed-article-88-draft'));

check('ID self-ref #88 (ini)', str_contains($id, '#88 (ini)'));
check('EN self-ref #88 (this article)', str_contains($en, '#88 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 5', substr_count($id, '<figure') >= 5 && substr_count($en, '<figure') >= 5);

check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('tools-first Upload IDE', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'Arduino IDE') && str_contains($id, 'Upload'));
check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Buka') && str_contains($id, 'GPIO 2'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Open') && str_contains($en, 'GPIO 2'));
check('how to test IDE Upload ID', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload EN', str_contains($en, 'How to test the commands above') && str_contains($en, 'Arduino IDE'));

check('IDE asset', str_contains($id, 'fs11-ide-overview-cite.png') && is_file(__DIR__.'/../public/images/fsiot/fs11-ide-overview-cite.png'));
check('board overview', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('kit LED asset', str_contains($id, 'kit-led-5mm.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-led-5mm.jpg'));
check('kit resistor asset', str_contains($id, 'kit-resistor-220ohm.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-resistor-220ohm.jpg'));
check('Arduino pinMode cite', str_contains($id, 'functions/digital-io/pinmode') && str_contains($en, 'functions/digital-io/pinmode'));
check('Arduino digitalWrite cite', str_contains($id, 'functions/digital-io/digitalwrite') && str_contains($en, 'functions/digital-io/digitalwrite'));
check('Espressif DevKitC-1 cite', str_contains($id, 'boards/ESP32-DevKitC-1.html') && str_contains($en, 'boards/ESP32-DevKitC-1.html'));
check('Wikimedia IDE cite', str_contains($id, 'Ide-2-overview.png') && str_contains($en, 'Ide-2-overview.png'));
check('KI diagram cite', str_contains($id, 'buatan Koding Indonesia') && str_contains($en, 'diagram by Koding Indonesia'));

check('sketch FS18_blink', str_contains($id, 'FS18_blink') && str_contains($en, 'FS18_blink'));
check('pinMode OUTPUT', str_contains($id, 'pinMode') && str_contains($id, 'OUTPUT') && str_contains($en, 'pinMode'));
check('digitalWrite HIGH LOW', str_contains($id, 'digitalWrite') && str_contains($id, 'HIGH') && str_contains($id, 'LOW'));
check('GPIO 2 practice pin', str_contains($id, 'GPIO 2') && str_contains($en, 'GPIO 2'));
check('220 ohm resistor', str_contains($id, '220') && str_contains($en, '220'));
check('BUILDER phase mention', str_contains($id, 'BUILDER') && str_contains($en, 'BUILDER'));
check('delay 1000 blink', str_contains($id, 'delay(1000)') && str_contains($en, 'delay(1000)'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-blink-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-blink-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-blink-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-blink-checklist-items'), '<li>') >= 10);
check('interactive blink checklist wired', str_contains($show, 'initFsiotBlinkChecklist') && str_contains($show, 'fsiot-blink-checklist'));
check('blink checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_blink_badge'));
check('blink checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_blink_badge'));

check('soft mention FS-17', str_contains($id, 'FS-17') && str_contains($en, 'FS-17'));
check('soft mention FS-09', str_contains($id, 'FS-09') && str_contains($en, 'FS-09'));
check('soft bridge FS-19', str_contains($id, 'FS-19') && str_contains($en, 'FS-19'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL."$pass pass / $fail fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
