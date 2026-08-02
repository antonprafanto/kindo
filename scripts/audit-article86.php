<?php

/**
 * Local audit for Article86Seeder (FS-16) — pre-launch draft.
 * Run: php scripts/audit-article86.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article86Seeder;

$ref = new ReflectionClass(Article86Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article86Seeder.php');
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
check('slug fullstack-iot-fungsi', str_contains($src, 'fullstack-iot-fungsi'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner for'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-86'));
check('seed route exists', str_contains($routes, 'seed-article-86-draft'));

check('ID self-ref #86 (ini)', str_contains($id, '#86 (ini)'));
check('EN self-ref #86 (this article)', str_contains($en, '#86 (this article)'));
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
check('tools-first no PHP today', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'Serial Monitor'));

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Buka') && str_contains($id, 'Arduino IDE'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Open') && str_contains($en, 'Arduino IDE'));
check('how to test Serial not artisan', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Serial Monitor') && ! str_contains(strstr($id, 'Cara menguji perintah di atas'), 'php artisan serve'));
check('how to test code EN', str_contains($en, 'How to test the code above') && str_contains($en, 'Serial Monitor'));

check('Serial.begin 115200', str_contains($id, 'Serial.begin(115200)') && str_contains($en, 'Serial.begin(115200)'));
check('FS16_status both', str_contains($id, 'FS16_status') && str_contains($en, 'FS16_status'));
check('cetakStatus teaching', str_contains($id, 'cetakStatus(int suhu)'));
check('printStatus teaching EN', str_contains($en, 'printStatus(int temp)'));
check('return intro', str_contains($id, 'return') && str_contains($en, 'return'));
check('wrong place warning', str_contains($id, 'SALAH') && str_contains($en, 'WRONG'));
check('IDE overview reused', str_contains($id, 'fs11-ide-overview-cite.png') && is_file(__DIR__.'/../public/images/fsiot/fs11-ide-overview-cite.png'));
check('Wikimedia IDE cite', str_contains($id, 'commons.wikimedia.org/wiki/File:Ide-2-overview.png') && str_contains($en, 'commons.wikimedia.org/wiki/File:Ide-2-overview.png'));
check('board overview reused', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('Espressif cite', str_contains($id, 'docs.espressif.com') && str_contains($en, 'docs.espressif.com'));
check('Arduino functions cite', str_contains($id, 'learn/programming/functions') && str_contains($en, 'learn/programming/functions'));
check('KI diagram cite', str_contains($id, 'buatan Koding Indonesia') && str_contains($en, 'diagram by Koding Indonesia'));
check('Serial shows three labels ID', str_contains($id, 'status: DINGIN') && str_contains($id, 'status: NORMAL') && str_contains($id, 'status: PANAS'));
check('Serial shows three labels EN', str_contains($en, 'status: COLD') && str_contains($en, 'status: NORMAL') && str_contains($en, 'status: HOT'));
check('place wording sejajar ID', str_contains($id, 'sejajar dengan'));
check('Simpan sebagai clarified', str_contains($id, 'Simpan sebagai') && str_contains($id, 'Save As'));
check('salin-tempel wording', str_contains($id, 'salin-tempel'));
preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-fn-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-fn-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-fn-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-fn-checklist-items'), '<li>') >= 10);
check('interactive fn checklist wired', str_contains($show, 'initFsiotFnChecklist') && str_contains($show, 'fsiot-fn-checklist'));
check('fn checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_fn_badge'));
check('fn checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_fn_badge'));

check('soft mention FS-15', str_contains($id, 'FS-15') && str_contains($en, 'FS-15'));
check('soft bridge FS-17', str_contains($id, 'FS-17') && str_contains($en, 'FS-17'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan serve', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('escaped comparisons in code', str_contains($id, 'suhu &lt; 20') && str_contains($id, 'suhu &lt;= 30'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
