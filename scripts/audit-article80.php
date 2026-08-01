<?php

/**
 * Local audit for Article80Seeder (FS-10) — awam-friendly draft.
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
check('title without awam stamp', ! str_contains($src, 'untuk awam') && ! str_contains($src, 'for beginners'));
check('seo without Awam/Beginner stamp', ! str_contains($src, 'Analog Awam') && ! str_contains($src, 'Beginner Digital'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-80'));
check('seed route exists', str_contains($routes, 'seed-article-80-draft'));

check('ID self-ref #80 (ini)', str_contains($id, '#80 (ini)'));
check('EN self-ref #80 (this article)', str_contains($en, '#80 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('neutral tip labels ID', str_contains($id, 'Analogi:') && str_contains($id, 'Intinya:') && str_contains($id, 'Tips:'));
check('neutral tip labels EN', str_contains($en, 'Analogy:') && str_contains($en, 'In short:') && str_contains($en, 'Tip:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 13', substr_count($id, '<figure') >= 13 && substr_count($en, '<figure') >= 13);

check('workflow SVG', str_contains($id, 'Alur hari ini') && str_contains($en, "Today's flow"));
check('pinout image', str_contains($id, 'esp32-devkitc-1-pinlayout.jpg') && str_contains($en, 'esp32-devkitc-1-pinlayout.jpg'));
check('jumper image', str_contains($id, 'kit-jumper-wires.jpg') && str_contains($en, 'kit-jumper-wires.jpg'));
check('multimeter image', str_contains($id, 'kit-multimeter.jpg') && str_contains($en, 'kit-multimeter.jpg'));
check('no V DC glyph', ! str_contains($id, 'V⎓') && ! str_contains($en, 'V⎓') && ! str_contains($src, 'V⎓'));
check('V DC wording', str_contains($id, 'V DC') && str_contains($en, 'V DC'));
check('measurement table ID', str_contains($id, 'tabel ukur tombol') && str_contains($id, '<table'));
check('measurement table EN', str_contains($en, 'measurement table') && str_contains($en, '<table'));
check('checklist interaktif mention', str_contains($id, 'checklist interaktif') && str_contains($en, 'interactive checklist'));
check('open multimeter first', str_contains($id, 'Buka alat ini dulu') && str_contains($en, 'Open this tool first'));
check('cara pakai tools-first ID', str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'browser') && str_contains($id, 'multimeter'));
check('how to use tools-first EN', str_contains($en, 'How to use this article') && str_contains($en, 'browser') && str_contains($en, 'multimeter'));
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
check('10k color bands ID EYD cokelat', str_contains($id, 'cokelat – hitam – oranye') || str_contains($id, 'cokelat-hitam-oranye'));
check('no coklat spelling', ! str_contains($id, 'coklat'));
check('10k color bands EN', str_contains($en, 'brown – black – orange') || str_contains($en, 'brown-black-orange'));
check('floating pin both', str_contains($id, 'mengambang') && str_contains($en, 'float'));
check('kit button image', str_contains($id, 'kit-tactile-button.jpg') && str_contains($en, 'kit-tactile-button.jpg'));
check('button citation BUTA', str_contains($id, 'BUTA-06-X-STAN-01'));
check('breadboard image', str_contains($id, 'kit-breadboard.jpg') && str_contains($en, 'kit-breadboard.jpg'));
check('10k resistor photo', str_contains($id, 'kit-resistor-10kohm.jpg') && str_contains($en, 'kit-resistor-10kohm.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-resistor-10kohm.jpg'));
check('10k resistor Commons cite', str_contains($id, 'Ceramic_Composition_Resistor_10k.png') && str_contains($en, 'Ceramic_Composition_Resistor_10k.png'));
check('pull diagram file', str_contains($id, 'fs10-pullup-pulldown.svg') && is_file(__DIR__.'/../public/images/fsiot/fs10-pullup-pulldown.svg'));
check('pull diagram landscape caption ID', str_contains($id, 'kotak hijau') && str_contains($id, 'Kanan = pull-down'));
check('pull diagram landscape caption EN', str_contains($en, 'green box') && str_contains($en, 'Right = pull-down'));
check('wiring H2 Indonesian', str_contains($id, 'Wiring langkah demi langkah'));
check('button legs SVG', str_contains($id, '4 kaki tombol') && str_contains($en, '4 button legs'));
check('main wiring diagram', str_contains($id, 'Gambar utama') && str_contains($en, 'Main diagram'));
check('main wiring photo file', str_contains($id, 'fs10-button-pulldown-wiring.png') && str_contains($en, 'fs10-button-pulldown-wiring.png') && is_file(__DIR__.'/../public/images/fsiot/fs10-button-pulldown-wiring.png'));
check('label wiring SVG helper', str_contains($id, 'fs10-button-pulldown-wiring.svg') && str_contains($en, 'fs10-button-pulldown-wiring.svg') && is_file(__DIR__.'/../public/images/fsiot/fs10-button-pulldown-wiring.svg'));
check('label SVG caption ID', str_contains($id, 'Skema berlabel (bantu baca foto)'));
check('label SVG caption EN', str_contains($en, 'Labeled schematic (helps read the photo)'));
check('photo orientation ID', str_contains($id, 'Orientasi foto') && str_contains($id, 'F–J') && str_contains($id, 'A–E'));
check('photo orientation EN', str_contains($en, 'Photo orientation') && str_contains($en, 'F–J') && str_contains($en, 'A–E'));
check('photo columns 3 and 5 ID', str_contains($id, 'kolom 3') && str_contains($id, 'kolom 5'));
check('photo columns 3 and 5 EN', str_contains($en, 'columns 3') && str_contains($en, 'column 5'));
check('wiring labeled legs ID', str_contains($id, 'kaki A tombol') && str_contains($id, 'Ke GND') && str_contains($id, 'titik sinyal (S)'));
check('wiring labeled legs EN', str_contains($en, 'button leg A') && str_contains($en, 'To GND') && str_contains($en, 'signal node (S)'));
check('no abstract circled step numbers in wiring', ! str_contains($id, '① ke 3V3') && ! str_contains($en, '① to 3V3'));
check('no GPIO signal jumper prep', str_contains($id, 'belum jumper ke GPIO') && str_contains($en, 'no GPIO jumper yet'));
check('no GPIO wire yet caption', str_contains($id, 'belum ada kabel ke pin GPIO') && str_contains($en, 'no wire to any GPIO'));
check('still multimeter first', str_contains($id, 'cukup ukur tegangan') && str_contains($en, 'only measure voltage'));
check('no Arduino upload', str_contains($id, 'Belum upload sketch') && str_contains($en, 'No sketch upload'));
check('wrong resistor mistake', str_contains($id, '220 Ω (FS-09)') && str_contains($en, '220 Ω (FS-09)'));
check('kesalahan heading EYD', str_contains($id, 'Kesalahan yang sering terjadi') && ! str_contains($id, 'Kesalahan umum pemula'));
check('common mistakes EN heading', str_contains($en, 'Common mistakes') && ! str_contains($en, 'Common beginner mistakes'));
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
check('menguji bukan sintaks', str_contains($id, 'bukan perintah sintaks') && str_contains($en, 'not syntax commands'));

check('No residual Indo in EN: Pendahuluan', ! str_contains($en, 'Pendahuluan'));
check('No residual Indo in EN: Persiapan', ! str_contains($en, 'Persiapan'));
check('No residual Indo in EN: Kesalahan yang sering', ! str_contains($en, 'Kesalahan yang sering'));
check('No residual Indo in EN: gangguan in EN body', ! preg_match('/<p[^>]*>[^<]*gangguan/', $en));

echo PHP_EOL."Result: {$pass} pass, {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
