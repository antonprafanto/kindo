<?php

/**
 * Local audit for Article89Seeder (FS-19) — pre-launch draft.
 * Run: php scripts/audit-article89.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article89Seeder;

$ref = new ReflectionClass(Article89Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article89Seeder.php');
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
check('slug fullstack-iot-tombol-debounce', str_contains($src, 'fullstack-iot-tombol-debounce'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner for'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-89'));
check('seed route exists', str_contains($routes, 'seed-article-89-draft'));

check('ID self-ref #89 (ini)', str_contains($id, '#89 (ini)'));
check('EN self-ref #89 (this article)', str_contains($en, '#89 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 6', substr_count($id, '<figure') >= 6 && substr_count($en, '<figure') >= 6);

check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('tools-first Upload IDE', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload ID', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload EN', str_contains($en, 'How to test the commands above') && str_contains($en, 'Arduino IDE'));

check('IDE asset', str_contains($id, 'fs11-ide-overview-cite.png') && is_file(__DIR__.'/../public/images/fsiot/fs11-ide-overview-cite.png'));
check('board overview', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('kit button asset', str_contains($id, 'kit-tactile-button.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-tactile-button.jpg'));
check('kit LED asset', str_contains($id, 'kit-led-5mm.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-led-5mm.jpg'));
check('Arduino digitalRead cite', str_contains($id, 'functions/digital-io/digitalread') && str_contains($en, 'functions/digital-io/digitalread'));
check('Arduino millis cite', str_contains($id, 'functions/time/millis') && str_contains($en, 'functions/time/millis'));
check('Espressif DevKitC-1 cite', str_contains($id, 'boards/ESP32-DevKitC-1.html') && str_contains($en, 'boards/ESP32-DevKitC-1.html'));
check('KI diagram cite', str_contains($id, 'buatan Koding Indonesia') && str_contains($en, 'diagram by Koding Indonesia'));

check('sketch FS19_btn_debounce', str_contains($id, 'FS19_btn_debounce') && str_contains($en, 'FS19_btn_debounce'));
check('digitalRead', str_contains($id, 'digitalRead') && str_contains($en, 'digitalRead'));
check('INPUT_PULLUP', str_contains($id, 'INPUT_PULLUP') && str_contains($en, 'INPUT_PULLUP'));
check('millis debounce', str_contains($id, 'millis') && str_contains($id, 'DEBOUNCE'));
check('GPIO 27 button', str_contains($id, 'GPIO 27') && str_contains($en, 'GPIO 27'));
check('GPIO 2 LED', str_contains($id, 'GPIO 2') && str_contains($en, 'GPIO 2'));
check('BUILDER soft via FS-18', str_contains($id, 'FS-18') && str_contains($en, 'FS-18'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-btn-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-btn-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-btn-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-btn-checklist-items'), '<li>') >= 10);
check('interactive btn checklist wired', str_contains($show, 'initFsiotBtnChecklist') && str_contains($show, 'fsiot-btn-checklist'));
check('btn checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_btn_badge'));
check('btn checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_btn_badge'));

check('soft mention FS-10', str_contains($id, 'FS-10') && str_contains($en, 'FS-10'));
check('soft mention FS-15', str_contains($id, 'FS-15') && str_contains($en, 'FS-15'));
check('soft bridge FS-20', str_contains($id, 'FS-20') && str_contains($en, 'FS-20'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL."$pass pass / $fail fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
