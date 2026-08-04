<?php

/**
 * Local audit for Article94Seeder (FS-24) — pre-launch draft.
 * Run: php scripts/audit-article94.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article94Seeder;

$ref = new ReflectionClass(Article94Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article94Seeder.php');
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
check('slug fullstack-iot-otomasi-lokal-panas-relay', str_contains($src, 'fullstack-iot-otomasi-lokal-panas-relay'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner for'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-94'));
check('seed route exists', str_contains($routes, 'seed-article-94-draft'));

check('ID self-ref #94 (ini)', str_contains($id, '#94 (ini)'));
check('EN self-ref #94 (this article)', str_contains($en, '#94 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 9', substr_count($id, '<figure') >= 9 && substr_count($en, '<figure') >= 9);

check('wiring PNG asset', str_contains($id, 'fs24-otomasi-wiring.png') && is_file(__DIR__.'/../public/images/fsiot/fs24-otomasi-wiring.png'));
check('breadboard PNG asset', str_contains($id, 'fs24-otomasi-breadboard.png') && is_file(__DIR__.'/../public/images/fsiot/fs24-otomasi-breadboard.png'));
check('helper DHT breadboard', str_contains($id, 'fs21-dht22-breadboard.png') && is_file(__DIR__.'/../public/images/fsiot/fs21-dht22-breadboard.png'));
check('helper relay breadboard', str_contains($id, 'fs23-relay-breadboard.png') && is_file(__DIR__.'/../public/images/fsiot/fs23-relay-breadboard.png'));
check('Gambar utama label ID', str_contains($id, 'Gambar utama'));
check('Main figure label EN', str_contains($en, 'Main figure'));
check('Skema bantu ID', str_contains($id, 'Skema bantu'));
check('Helper schematic EN', str_contains($en, 'Helper schematic'));
check('Acuan fisik ID', str_contains($id, 'Acuan fisik'));
check('Physical reference EN', str_contains($en, 'Physical'));
check('DHTTYPE tip for DHT11', str_contains($id, 'DHTTYPE') && str_contains($en, 'DHTTYPE'));
check('S/+/- aliases ID', str_contains($id, 'S / + / −'));
check('IDE caption warns AnalogReadSerial', str_contains($id, 'AnalogReadSerial') && str_contains($en, 'AnalogReadSerial'));
check('Serial panel SVG 115200', str_contains($id, 'Baud: 115200') && (str_contains($id, 'kipas=') || str_contains($id, 'ON')));
check('hysteresis content', str_contains($id, 'histeresis') && str_contains($en, 'hysteresis'));
check('AMBANG_ON / thresholds', str_contains($id, 'AMBANG_ON') && (str_contains($en, 'THRESH_ON') || str_contains($en, 'AMBANG_ON') || str_contains($en, '30.0')));
check('EYD otomasi not automasi', str_contains($id, 'otomasi') && ! str_contains($id, 'automasi'));
check('EYD kelembapan', str_contains($id, 'kelembapan') && ! str_contains($id, 'kelembaban'));
check('open IDE first wording', str_contains($id, 'Buka Arduino IDE dulu') && str_contains($en, 'Open Arduino IDE first'));
check('cover asset exists', is_file(__DIR__.'/../public/images/fsiot/fs24-cover-otomasi.jpg'));
check('cover set-if-blank in seeder', str_contains($src, 'fs24-cover-otomasi.jpg') && str_contains($src, 'cover_image'));
check('kit DHT asset', str_contains($id, 'kit-dht22.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-dht22.jpg'));
check('kit relay asset', str_contains($id, 'kit-relay-5v.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-relay-5v.jpg'));

check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('tools-first Upload IDE', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'Arduino IDE'));
check('Library Manager mentioned', str_contains($id, 'Library Manager') && str_contains($en, 'Library Manager'));
check('how to test IDE Upload ID', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload EN', str_contains($en, 'How to test the commands above') && str_contains($en, 'Arduino IDE'));
check('AC 220V warning ID', str_contains($id, '220V') && str_contains($id, 'dilarang'));
check('AC 220V warning EN', str_contains($en, '220V') && (str_contains($en, 'forbidden') || str_contains($en, 'not AC')));

check('IDE asset', str_contains($id, 'fs11-ide-overview-cite.png') && is_file(__DIR__.'/../public/images/fsiot/fs11-ide-overview-cite.png'));
check('board overview', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('Commons cite', str_contains($id, 'commons.wikimedia.org') && str_contains($en, 'commons.wikimedia.org'));
check('KI diagram cite', str_contains($id, 'buatan Koding Indonesia') && str_contains($en, 'diagram by Koding Indonesia'));
check('digitalWrite cite', str_contains($id, 'digitalWrite') && str_contains($en, 'digitalWrite'));

check('sketch FS24_panas_relay', str_contains($id, 'FS24_panas_relay') && str_contains($en, 'FS24_panas_relay'));
check('GPIO 4 DHT', str_contains($id, 'GPIO 4') && str_contains($en, 'GPIO 4'));
check('GPIO 26 relay', str_contains($id, 'GPIO 26') && str_contains($en, 'GPIO 26'));
check('aktif LOW / ACTIVE_LOW', (str_contains($id, 'AKTIF_LOW') || str_contains($id, 'aktif LOW')) && (str_contains($en, 'ACTIVE_LOW') || str_contains($en, 'active LOW')));
check('prereq soft FS-21 FS-23 FS-14', str_contains($id, 'FS-21') && str_contains($id, 'FS-23') && str_contains($id, 'FS-14'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-auto-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-auto-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-auto-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-auto-checklist-items'), '<li>') >= 10);
check('interactive auto checklist wired', str_contains($show, 'initFsiotAutoChecklist') && str_contains($show, 'fsiot-auto-checklist'));
check('auto checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_auto_badge'));
check('auto checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_auto_badge'));

check('soft bridge FS-25', str_contains($id, 'FS-25') && str_contains($en, 'FS-25'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL."$pass pass / $fail fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
