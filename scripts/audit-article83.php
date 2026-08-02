<?php

/**
 * Local audit for Article83Seeder (FS-13) — pre-launch draft.
 * Run: php scripts/audit-article83.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article83Seeder;

$ref = new ReflectionClass(Article83Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article83Seeder.php');
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
check('slug fullstack-iot-serial-monitor-debug', str_contains($src, 'fullstack-iot-serial-monitor-debug'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner Serial'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-83'));
check('seed route exists', str_contains($routes, 'seed-article-83-draft'));

check('ID self-ref #83 (ini)', str_contains($id, '#83 (ini)'));
check('EN self-ref #83 (this article)', str_contains($en, '#83 (this article)'));
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
check('tools-first no PHP today', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'Serial Monitor'));

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Buka') && str_contains($id, 'Arduino IDE'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Open') && str_contains($en, 'Arduino IDE'));
check('how to test Serial not artisan', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Serial Monitor') && ! str_contains(strstr($id, 'Cara menguji perintah di atas'), 'php artisan serve'));
check('how to test code EN', str_contains($en, 'How to test the code above') && str_contains($en, 'Serial Monitor'));

check('Serial.begin 115200', str_contains($id, 'Serial.begin(115200)') && str_contains($en, 'Serial.begin(115200)'));
check('delay 1000', str_contains($id, 'delay(1000)') && str_contains($en, 'delay(1000)'));
check('FS13_detak both', str_contains($id, 'FS13_detak') && str_contains($en, 'FS13_detak'));
check('baud 115200 both', str_contains($id, '115200') && str_contains($en, '115200'));
check('no IDE Commons screenshots', ! str_contains($id, 'fs11-ide-overview') && ! str_contains($id, 'Ide-2-overview'));
check('board overview reused', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('setup vs loop SVG', str_contains($id, 'setup vs loop') || str_contains($id, 'setup()'));
check('flood SVG', str_contains($id, 'delay(1000)') && str_contains($id, 'banjir'));
check('port SVG', str_contains($id, 'satu port') || str_contains($id, 'Satu kabel USB'));
check('panel SVG', str_contains($id, 'contoh log detak') || str_contains($id, 'Serial Monitor (IDE 2)'));
check('Arduino Docs Serial cite', str_contains($id, 'docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor') && str_contains($en, 'docs.arduino.cc/software/ide-v2/tutorials/ide-v2-serial-monitor'));
check('ID H2 no English flood alone', str_contains($id, 'Jangan banjir teks') && ! str_contains($id, '<h2>Jangan flood'));
check('port SVG SALAH/BENAR labels', str_contains($id, '>SALAH</text>') && str_contains($id, '>BENAR</text>'));
check('EN port WRONG/RIGHT labels', str_contains($en, '>WRONG</text>') && str_contains($en, '>RIGHT</text>'));
check('board caption highlights USB (6)', str_contains($id, '(6)') && str_contains($id, 'EN (7)'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-sm-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-sm-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-sm-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-sm-checklist-items'), '<li>') >= 10);
check('interactive sm checklist wired', str_contains($show, 'initFsiotSerialChecklist') && str_contains($show, 'fsiot-sm-checklist'));
check('sm checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_sm_badge'));
check('sm checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_sm_badge'));

check('soft mention FS-12', str_contains($id, 'FS-12') && str_contains($en, 'FS-12'));
check('soft bridge FS-14', str_contains($id, 'FS-14') && str_contains($en, 'FS-14'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan serve', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
