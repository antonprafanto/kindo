<?php

/**
 * Local audit for Article79Seeder (FS-09) — pre-launch draft.
 * Run: php scripts/audit-article79.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article79Seeder;

$ref = new ReflectionClass(Article79Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article79Seeder.php');
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
check('slug fullstack-iot-led-resistor-di-breadboard', str_contains($src, 'fullstack-iot-led-resistor-di-breadboard'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Breadboard Awam') && ! str_contains($src, 'Beginner LED'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-79'));
check('seed route exists', str_contains($routes, 'seed-article-79-draft'));

check('ID self-ref #79 (ini)', str_contains($id, '#79 (ini)'));
check('EN self-ref #79 (this article)', str_contains($en, '#79 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 7', substr_count($id, '<figure') >= 7 && substr_count($en, '<figure') >= 7);
check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('tools-first no PHP today', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'browser'));

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Cabut USB'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Unplug USB'));
check('breadboard wiring today ID', str_contains($id, 'Wiring langkah') || str_contains($id, 'wiring'));
check('breadboard wiring today EN', str_contains($en, 'Step-by-step wiring') || str_contains($en, 'wiring'));
check('3V3 pin power ID', str_contains($id, 'pin 3V3') || str_contains($id, 'pin <strong>3V3</strong>'));
check('3V3 pin power EN', str_contains($en, '3V3 pin') || str_contains($en, '<strong>3V3</strong>'));
check('column 2 both', str_contains($id, 'kolom 2') && str_contains($en, 'column 2'));
check('ESP32 on breadboard caption', str_contains($id, 'ESP32 dipasang di breadboard') && str_contains($en, 'ESP32 sits on the breadboard'));
check('main wiring photo file', str_contains($id, 'fs09-led-breadboard-wiring.png') && str_contains($en, 'fs09-led-breadboard-wiring.png') && is_file(__DIR__.'/../public/images/fsiot/fs09-led-breadboard-wiring.png'));
check('main wiring caption Gambar utama', str_contains($id, 'Gambar utama') && str_contains($en, 'Main diagram'));
check('photo orientation F-J A-E ID', str_contains($id, 'Orientasi foto') && str_contains($id, 'F–J') && str_contains($id, 'A–E'));
check('photo orientation F-J A-E EN', str_contains($en, 'Photo orientation') && str_contains($en, 'F–J') && str_contains($en, 'A–E'));
check('photo columns 2 and 7 ID', str_contains($id, 'kolom 2') && str_contains($id, 'kolom 7') && str_contains($id, 'kolom 8'));
check('photo columns 2 and 7 EN', str_contains($en, 'column 2') && str_contains($en, 'column 7') && str_contains($en, 'column 8'));
check('short warning in main diagram', str_contains($id, 'Jangan sambungkan 3V3 dan GND') && str_contains($en, 'Never put 3V3 and GND'));
check('photo citation Koding Indonesia', str_contains($id, 'foto rangkaian buatan Koding Indonesia') && str_contains($en, 'circuit photo by Koding Indonesia'));
check('no abstract SVG as main wiring', ! str_contains($id, 'Gambar utama — LED menyala dari 3V3 (belum coding)</text>'));
check('kit images', str_contains($id, 'kit-breadboard.jpg') && str_contains($id, 'kit-jumper-wires.jpg') && str_contains($id, 'kit-led-5mm.jpg') && str_contains($id, 'kit-resistor-220ohm.jpg'));
check('220ohm on disk', is_file(__DIR__.'/../public/images/fsiot/kit-resistor-220ohm.jpg'));
check('pinout photo', str_contains($id, 'esp32-devkitc-1-pinlayout.jpg') && str_contains($en, 'esp32-devkitc-1-pinlayout.jpg'));
check('find 2 pins caption', str_contains($id, 'Cari 2 pin ini di board kamu') && str_contains($en, 'Find these 2 pins on your board'));
check('flow SVG still present', str_contains($id, 'Buatan Koding Indonesia') && str_contains($id, '220Ω'));
check('no 220R jargon', ! str_contains($id, '220R') && ! str_contains($en, '220R'));
preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-led-circuit-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-led-circuit-checklist"'));
check('checklist markers', str_contains($id, 'id="fsiot-led-circuit-checklist"') && str_contains($id, 'id="fsiot-led-circuit-checklist-items"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-led-circuit-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-led-circuit-checklist-items'), '<li>') >= 10);

check('soft mention FS-08', str_contains($id, 'FS-08') && str_contains($en, 'FS-08'));
check('soft bridge FS-10', str_contains($id, 'FS-10') && str_contains($en, 'FS-10'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan in practice', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('no Arduino IDE upload', str_contains($id, 'belum upload sketch') && str_contains($en, 'no sketch upload'));
check('unplug jumper practice', str_contains($id, 'cabut jumper') && str_contains($en, 'unplug'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
