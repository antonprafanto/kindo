<?php

/**
 * Local audit for Article78Seeder (FS-08) — pre-launch draft.
 * Run: php scripts/audit-article78.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article78Seeder;

$ref = new ReflectionClass(Article78Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article78Seeder.php');
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
check('slug fullstack-iot-listrik-mini-tegangan-arus-resistansi', str_contains($src, 'fullstack-iot-listrik-mini-tegangan-arus-resistansi'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('title Listrik mini tanpa awam', str_contains($src, "'title'              => 'Listrik mini:") && str_contains($src, 'Mini electricity:'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-78'));
check('seed route exists', str_contains($routes, 'seed-article-78-draft'));

check('ID self-ref #78 (ini)', str_contains($id, '#78 (ini)'));
check('EN self-ref #78 (this article)', str_contains($en, '#78 (this article)'));
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
check('tools-first no PHP today', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today'));

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Kalkulator HP'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Phone calculator'));
check('no breadboard wiring today ID', str_contains($id, 'Tidak ada wiring breadboard'));
check('no breadboard wiring today EN', str_contains($en, 'No breadboard wiring'));
check('water analogy both', str_contains($id, 'Analogi air') && str_contains($en, 'Water analogy'));
check('Ohm formula both', str_contains($id, 'V = I x R') && str_contains($en, 'V = I x R'));
check('220 and 330 both', str_contains($id, '220') && str_contains($id, '330') && str_contains($en, '220') && str_contains($en, '330'));
check('130 ohm result', str_contains($id, '130 ohm') && str_contains($en, '130 ohm'));
check('LED image', str_contains($id, 'kit-led-5mm.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-led-5mm.jpg'));
check('220ohm resistor image', str_contains($id, 'kit-resistor-220ohm.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-resistor-220ohm.jpg'));
check('color code chart image', str_contains($id, 'resistor-color-code.jpg') && is_file(__DIR__.'/../public/images/fsiot/resistor-color-code.jpg'));
check('Commons citations', str_contains($id, 'commons.wikimedia.org') && str_contains($id, '220_ohms') && str_contains($id, 'Resistor_color_code.png'));
check('SVG water + circuit LED label', str_contains($id, 'Buatan Koding Indonesia') && str_contains($id, '>LED</text>') && str_contains($id, '220Ω'));
check('no 220R jargon', ! str_contains($id, '220R') && ! str_contains($en, '220R'));
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('calc root survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-resistor-calc-root"'));
check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-electric-checklist-items"'));
check('checklist markers', str_contains($id, 'id="fsiot-electric-checklist"') && str_contains($id, 'id="fsiot-electric-checklist-items"'));
check('checklist has 8 items ID', substr_count(strstr($id, 'fsiot-electric-checklist-items'), '<li>') >= 8);
check('checklist has 8 items EN', substr_count(strstr($en, 'fsiot-electric-checklist-items'), '<li>') >= 8);

check('soft mention FS-07', str_contains($id, 'FS-07') && str_contains($en, 'FS-07'));
check('soft bridge FS-09', str_contains($id, 'FS-09') && str_contains($en, 'FS-09'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan in practice', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('no Laragon as main tool', ! str_contains($id, 'Laragon') || str_contains($id, 'Tidak perlu hari ini'));
check('measurement table', str_contains($id, '<table') && str_contains($en, '<table'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
