<?php

/**
 * Local audit for Article95Seeder (FS-25) — pre-launch draft.
 * Run: php scripts/audit-article95.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article95Seeder;

$ref = new ReflectionClass(Article95Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article95Seeder.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');
$show = file_get_contents(__DIR__.'/../resources/views/articles/show.blade.php');
$deploy = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

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
check('slug fullstack-iot-pir-gerak', str_contains($src, 'fullstack-iot-pir-gerak'));
check('seed route exists', str_contains($routes, 'seed-article-95-draft'));
check('deploy seed step', str_contains($deploy, 'seed-article-95-draft'));
check('ftp allowlist fs25', str_contains($deploy, 'fs25-pir-wiring.png') && str_contains($deploy, 'fs25-pir-breadboard.png') && str_contains($deploy, 'kit-pir-hcsr501.jpg'));

check('ID self-ref #95 (ini)', str_contains($id, '#95 (ini)'));
check('EN self-ref #95 (this article)', str_contains($en, '#95 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 6', substr_count($id, '<figure') >= 6 && substr_count($en, '<figure') >= 6);

check('breadboard PNG asset', str_contains($id, 'fs25-pir-breadboard.png') && is_file(__DIR__.'/../public/images/fsiot/fs25-pir-breadboard.png'));
check('wiring PNG asset', str_contains($id, 'fs25-pir-wiring.png') && is_file(__DIR__.'/../public/images/fsiot/fs25-pir-wiring.png'));
check('kit PIR asset', str_contains($id, 'kit-pir-hcsr501.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-pir-hcsr501.jpg'));
check('cover asset', is_file(__DIR__.'/../public/images/fsiot/fs25-cover-pir.jpg'));
check('Gambar utama label ID', str_contains($id, 'Gambar utama') && str_contains($id, 'fs25-pir-breadboard.png'));
check('Skema bantu label ID', str_contains($id, 'Skema bantu') && str_contains($id, 'fs25-pir-wiring.png'));
check('Main figure label EN', str_contains($en, 'Main figure') && str_contains($en, 'fs25-pir-breadboard.png'));
check('Helper schematic label EN', str_contains($en, 'Helper schematic') && str_contains($en, 'fs25-pir-wiring.png'));
check('IDE caption warns AnalogReadSerial', str_contains($id, 'AnalogReadSerial') && str_contains($en, 'AnalogReadSerial'));
check('Serial panel SVG 115200', str_contains($id, 'Baud: 115200'));
check('open IDE first wording', str_contains($id, 'Buka Arduino IDE dulu') && str_contains($en, 'Open Arduino IDE first'));
check('how to test IDE Upload ID', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload EN', str_contains($en, 'How to test the commands above') && str_contains($en, 'Arduino IDE'));
check('sketch FS25_pir_gerak', str_contains($id, 'FS25_pir_gerak') && str_contains($en, 'FS25_pir_gerak'));
check('GPIO 25 PIR', str_contains($id, 'GPIO 25') && str_contains($en, 'GPIO 25'));
check('GPIO 2 LED', str_contains($id, 'GPIO 2') && str_contains($en, 'GPIO 2'));
check('settle guidance', str_contains($id, 'settle') && str_contains($en, 'settle'));
check('HC-SR501', str_contains($id, 'HC-SR501') && str_contains($en, 'HC-SR501'));
check('Commons cite PIR', str_contains($id, 'commons.wikimedia.org') && str_contains($id, 'PIR-inexpensive'));
check('prereq soft FS-19 FS-14', str_contains($id, 'FS-19') && str_contains($id, 'FS-14'));
check('soft bridge FS-26', str_contains($id, 'FS-26') && str_contains($en, 'FS-26'));
check('EYD otomasi not automasi', ! str_contains($id, 'automasi'));
check('EYD potensiometer not potensio', str_contains($id, 'potensiometer') && ! preg_match('/(?<![a-z])potensio(?!meter)/i', $id));
check('no awam in wiring PNG recipe', ! str_contains(file_get_contents(__DIR__.'/../scripts/gen-fs25-assets.py'), 'awam'));
check('schema legend orange LED', str_contains(file_get_contents(__DIR__.'/../scripts/gen-fs25-assets.py'), 'oranye LED/GPIO 2'));
check('kit silkscreen tip ID', str_contains($id, 'Jangan tebak dari warna kabel foto') && str_contains($id, 'silkscreen'));
check('figures under Wiring H2', strpos($id, 'Wiring (bahasa manusia)') < strpos($id, 'kit-pir-hcsr501.jpg'));
check('schema color legend caption', str_contains($id, 'Warna di skema') && str_contains($id, 'oranye'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-pir-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-pir-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-pir-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-pir-checklist-items'), '<li>') >= 10);
check('interactive pir checklist wired', str_contains($show, 'initFsiotPirChecklist') && str_contains($show, 'fsiot-pir-checklist'));
check('pir checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_pir_badge'));
check('pir checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_pir_badge'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL."$pass pass / $fail fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
