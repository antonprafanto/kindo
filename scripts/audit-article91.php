<?php

/**
 * Local audit for Article91Seeder (FS-21) — pre-launch draft.
 * Run: php scripts/audit-article91.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article91Seeder;

$ref = new ReflectionClass(Article91Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article91Seeder.php');
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
check('slug fullstack-iot-sensor-dht22-serial', str_contains($src, 'fullstack-iot-sensor-dht22-serial'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner for'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-91'));
check('seed route exists', str_contains($routes, 'seed-article-91-draft'));

check('ID self-ref #91 (ini)', str_contains($id, '#91 (ini)'));
check('EN self-ref #91 (this article)', str_contains($en, '#91 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 7', substr_count($id, '<figure') >= 7 && substr_count($en, '<figure') >= 7);
check('wiring PNG asset', str_contains($id, 'fs21-dht22-wiring.png') && is_file(__DIR__.'/../public/images/fsiot/fs21-dht22-wiring.png'));
check('Gambar utama label ID', str_contains($id, 'Gambar utama'));
check('Main figure label EN', str_contains($en, 'Main figure'));
check('cover asset exists', is_file(__DIR__.'/../public/images/fsiot/fs21-cover-dht22.jpg'));
check('cover set-if-blank in seeder', str_contains($src, 'fs21-cover-dht22.jpg') && str_contains($src, 'cover_image'));
check('kit-dht22 asset', str_contains($id, 'kit-dht22.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-dht22.jpg'));

check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('tools-first Upload IDE', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload ID', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload EN', str_contains($en, 'How to test the commands above') && str_contains($en, 'Arduino IDE'));

check('IDE asset', str_contains($id, 'fs11-ide-overview-cite.png') && is_file(__DIR__.'/../public/images/fsiot/fs11-ide-overview-cite.png'));
check('board overview', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('Adafruit DHT cite', str_contains($id, 'adafruit') && str_contains($en, 'adafruit'));
check('KI diagram cite', str_contains($id, 'buatan Koding Indonesia') && str_contains($en, 'diagram by Koding Indonesia'));

check('sketch FS21_dht22_serial', str_contains($id, 'FS21_dht22_serial') && str_contains($en, 'FS21_dht22_serial'));
check('DHT22', str_contains($id, 'DHT22') && str_contains($en, 'DHT22'));
check('GPIO 4', str_contains($id, 'GPIO 4') && str_contains($en, 'GPIO 4'));
check('kelembapan ID (EYD)', str_contains($id, 'kelembapan') && ! str_contains($id, 'kelembaban'));
check('humidity EN', str_contains($en, 'humidity'));
check('Library Manager', str_contains($id, 'Library Manager') && str_contains($en, 'Library Manager'));
check('isnan', str_contains($id, 'isnan') && str_contains($en, 'isnan'));
check('BUILDER soft via FS-18', str_contains($id, 'FS-18') && str_contains($en, 'FS-18'));
check('soft mention FS-17', str_contains($id, 'FS-17') && str_contains($en, 'FS-17'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-dht-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-dht-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-dht-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-dht-checklist-items'), '<li>') >= 10);
check('interactive dht checklist wired', str_contains($show, 'initFsiotDhtChecklist') && str_contains($show, 'fsiot-dht-checklist'));
check('dht checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_dht_badge'));
check('dht checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_dht_badge'));

check('soft bridge FS-22', str_contains($id, 'FS-22') && str_contains($en, 'FS-22'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL."$pass pass / $fail fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
