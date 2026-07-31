<?php

/**
 * Local audit for Article80Seeder (FS-10) — pre-launch draft.
 * Run: php scripts/audit-article80.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article80Seeder;

$ref = new ReflectionClass(Article80Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article80Seeder.php');
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
check('slug fullstack-iot-digital-analog-high-low-pull-resistor', str_contains($src, 'fullstack-iot-digital-analog-high-low-pull-resistor'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains($routes, 'publish-article-80'));
check('seed route exists', str_contains($routes, 'seed-article-80-draft'));

check('ID self-ref #80 (ini)', str_contains($id, '#80 (ini)'));
check('EN self-ref #80 (this article)', str_contains($en, '#80 (this article)'));
check('ID Awam >= 6', substr_count($id, 'Awam:') + substr_count($id, 'Awam —') >= 6);
check('EN Beginner >= 6', substr_count($en, 'Beginner:') + substr_count($en, 'Beginner —') >= 6);
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 12', substr_count($id, '<figure') >= 12 && substr_count($en, '<figure') >= 12);

check('workflow SVG', str_contains($id, 'Alur hari ini') && str_contains($en, 'Today'));
check('pinout image', str_contains($id, 'esp32-devkitc-1-pinlayout.jpg') && str_contains($en, 'esp32-devkitc-1-pinlayout.jpg'));
check('jumper image', str_contains($id, 'kit-jumper-wires.jpg') && str_contains($en, 'kit-jumper-wires.jpg'));
check('multimeter image', str_contains($id, 'kit-multimeter.jpg') && str_contains($en, 'kit-multimeter.jpg'));
check('measurement table ID', str_contains($id, 'tabel ukur tombol') && str_contains($id, '<table'));
check('measurement table EN', str_contains($en, 'measurement table') && str_contains($en, '<table'));
check('checklist interaktif mention', str_contains($id, 'checklist interaktif') && str_contains($en, 'interactive checklist'));
check('open multimeter first', str_contains($id, 'buka alat ini dulu') && str_contains($en, 'open this tool first'));
check('beginner read order ID', str_contains($id, 'cara pakai artikel ini'));
check('beginner read order EN', str_contains($en, 'how to use this article'));
check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Cabut USB'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Unplug USB'));
check('kertas pena ID', str_contains($id, 'Kertas + pena'));
check('paper pen EN', str_contains($en, 'Paper + pen'));
check('GPIO expanded ID', str_contains($id, 'General Purpose Input/Output'));
check('GPIO expanded EN', str_contains($en, 'General Purpose Input/Output'));
check('multimeter test ID', str_contains($id, 'Uji dengan multimeter') && str_contains($id, 'V DC'));
check('multimeter test EN', str_contains($en, 'Test with a multimeter') && str_contains($en, 'V DC'));
check('HIGH LOW both', str_contains($id, 'HIGH') && str_contains($id, 'LOW') && str_contains($en, 'HIGH') && str_contains($en, 'LOW'));
check('pull-down 10k both', str_contains($id, 'pull-down') && str_contains($id, '10 kΩ') && str_contains($en, 'pull-down') && str_contains($en, '10 kΩ'));
check('pull-up preview FS-11', str_contains($id, 'pull-up internal') && str_contains($en, 'internal pull-ups'));
check('10k color bands ID', str_contains($id, 'coklat – hitam – oranye') || str_contains($id, 'coklat-hitam-oranye'));
check('10k color bands EN', str_contains($en, 'brown – black – orange') || str_contains($en, 'brown-black-orange'));
check('floating pin both', str_contains($id, 'mengambang') && str_contains($en, 'float'));
check('kit button image', str_contains($id, 'kit-tactile-button.jpg') && str_contains($en, 'kit-tactile-button.jpg'));
check('button citation BUTA', str_contains($id, 'BUTA-06-X-STAN-01'));
check('breadboard image', str_contains($id, 'kit-breadboard.jpg') && str_contains($en, 'kit-breadboard.jpg'));
check('10k resistor diagram', str_contains($id, 'fs10-resistor-10k.svg') && is_file(__DIR__.'/../public/images/fsiot/fs10-resistor-10k.svg'));
check('pull diagram file', str_contains($id, 'fs10-pullup-pulldown.svg') && is_file(__DIR__.'/../public/images/fsiot/fs10-pullup-pulldown.svg'));
check('button legs SVG', str_contains($id, '4 kaki tombol') && str_contains($en, '4 button legs'));
check('main wiring diagram', str_contains($id, 'Gambar utama') && str_contains($en, 'Main diagram'));
check('no Arduino upload', str_contains($id, 'Belum upload sketch') && str_contains($en, 'No sketch upload'));
check('wrong resistor mistake', str_contains($id, '220 Ω (FS-09)') && str_contains($en, '220 Ω (FS-09)'));
preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
$idFloat = $svgIdBlocks[0][2] ?? '';
check('no English noise in ID float SVG', ! str_contains($idFloat, '>noise<'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-signal-checklist-items"'));
check('checklist markers', str_contains($id, 'id="fsiot-signal-checklist"') && str_contains($id, 'id="fsiot-signal-checklist-items"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-signal-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-signal-checklist-items'), '<li>') >= 10);

check('soft mention FS-05', str_contains($id, 'FS-05') && str_contains($en, 'FS-05'));
check('soft mention FS-09', str_contains($id, 'FS-09') && str_contains($en, 'FS-09'));
check('soft bridge FS-11', str_contains($id, 'FS-11') && str_contains($en, 'FS-11'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('optional continuity FS-07', str_contains($id, 'continuity') && str_contains($id, 'FS-07'));
check('table survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), '<table') && str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'tabel ukur tombol'));
check('seo 10 kΩ spacing', str_contains($src, "'seo_description'") && str_contains($src, 'pull-down 10 kΩ'));
check('kesalahan >= 7', substr_count($id, '<li><strong>') >= 7);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan in practice', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));

check('No residual Indo in EN: Pendahuluan', ! str_contains($en, 'Pendahuluan'));
check('No residual Indo in EN: Persiapan', ! str_contains($en, 'Persiapan'));
check('No residual Indo in EN: Kesalahan umum', ! str_contains($en, 'Kesalahan umum'));
check('No residual Indo in EN: gangguan in EN body', ! preg_match('/<p[^>]*>[^<]*gangguan/', $en));

echo PHP_EOL."Result: {$pass} pass, {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
