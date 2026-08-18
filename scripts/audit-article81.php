<?php

/**
 * Local audit for Article81Seeder (FS-11) — pre-launch draft.
 * Run: php scripts/audit-article81.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article81Seeder;

$ref = new ReflectionClass(Article81Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article81Seeder.php');
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
check('slug fullstack-iot-sketch-setup-loop', str_contains($src, 'fullstack-iot-sketch-setup-loop'));
preg_match("/'seo_description'\\s*=>\\s*'([^']*)'/", $src, $seoDescId);
preg_match("/'seo_description_en'\\s*=>\\s*'([^']*)'/", $src, $seoDescEn);
check('seo_description ≤160', mb_strlen($seoDescId[1] ?? '') <= 160);
check('seo_description_en ≤160', mb_strlen($seoDescEn[1] ?? '') <= 160);
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner Sketch'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-81'));
check('seed route exists', str_contains($routes, 'seed-article-81-draft'));

check('ID self-ref #81 (ini)', str_contains($id, '#81 (ini)'));
check('EN self-ref #81 (this article)', str_contains($en, '#81 (this article)'));
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
check('tools-first no PHP today', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'Arduino IDE'));

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Buka Arduino IDE'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Open Arduino IDE'));
check('File Explorer mentioned ID', str_contains($id, 'File Explorer'));
check('File Explorer mentioned EN', str_contains($en, 'File Explorer'));
check('how to test IDE not artisan ID', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Arduino IDE') && ! str_contains(strstr($id, 'Cara menguji perintah di atas'), 'php artisan serve'));
check('how to test code EN', str_contains($en, 'How to test the code above') && str_contains($en, 'Arduino IDE'));

check('setup and loop both', str_contains($id, 'setup()') && str_contains($id, 'loop()') && str_contains($en, 'setup()') && str_contains($en, 'loop()'));
check('Verify Done compiling', str_contains($id, 'Done compiling') && str_contains($en, 'Done compiling'));
check('Upload Done uploading', str_contains($id, 'Done uploading') && str_contains($en, 'Done uploading'));
check('FS11_hello both', str_contains($id, 'FS11_hello') && str_contains($en, 'FS11_hello'));
check('do nothing practice', str_contains($id, 'do nothing') && str_contains($en, 'Do nothing'));
check('sketchbook Documents Arduino', str_contains($id, 'Documents\\Arduino') || str_contains($id, 'Documents\Arduino'));
check('comment // explained', str_contains($id, '//') && str_contains($id, 'Komentar'));

check('IDE overview image on disk', is_file(__DIR__.'/../public/images/fsiot/fs11-ide-overview-cite.png'));
check('board overview reused', is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('IDE image in body', str_contains($id, 'fs11-ide-overview-cite.png') && str_contains($en, 'fs11-ide-overview-cite.png'));
check('no misleading IDE 1.8 screenshots', ! str_contains($id, 'fs11-select-verify.png') && ! str_contains($id, 'fs11-select-upload.png') && ! str_contains($en, 'fs11-select-verify.png'));
check('Verify SVG IDE 2 ID', str_contains($id, 'Verify di Arduino IDE 2') && str_contains($id, 'Done compiling'));
check('Upload SVG IDE 2 ID', str_contains($id, 'Upload di Arduino IDE 2') && str_contains($id, 'Done uploading'));
check('Verify SVG IDE 2 EN', str_contains($en, 'Verify in Arduino IDE 2') && str_contains($en, 'Done compiling'));
check('Upload SVG IDE 2 EN', str_contains($en, 'Upload in Arduino IDE 2') && str_contains($en, 'Done uploading'));
check('warn not IDE 1.x ID', str_contains($id, 'bukan IDE 1.x'));
check('warn not IDE 1.x EN', str_contains($en, 'not IDE 1.x'));
check('IDE overview Commons cite', str_contains($id, 'Ide-2-overview.png') && str_contains($en, 'Ide-2-overview.png'));
check('Espressif board cite', str_contains($id, 'docs.espressif.com') && str_contains($en, 'docs.espressif.com'));
check('recipe SVG present', str_contains($id, 'Resep di dapur') && str_contains($en, 'Kitchen recipe'));
check('flow SVG present', str_contains($id, 'Alur kerja hari ini') && str_contains($en, "Today's workflow"));
check('sketchbook SVG present', str_contains($id, 'Sketchbook — satu folder') && str_contains($en, 'Sketchbook — one folder'));
check('ESP32 Dev Module reminder', str_contains($id, 'ESP32 Dev Module') && str_contains($en, 'ESP32 Dev Module'));

$show = file_get_contents(__DIR__.'/../resources/views/articles/show.blade.php');
check('interactive sketch checklist wired', str_contains($show, 'initFsiotSketchChecklist') && str_contains($show, 'fsiot-sketch-checklist'));
check('sketch checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_sk_badge'));
check('sketch checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_sk_badge'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-sketch-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-sketch-checklist"'));
check('checklist markers', str_contains($id, 'id="fsiot-sketch-checklist"') && str_contains($id, 'id="fsiot-sketch-checklist-items"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-sketch-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-sketch-checklist-items'), '<li>') >= 10);

check('soft mention FS-06', str_contains($id, 'FS-06') && str_contains($en, 'FS-06'));
check('soft mention FS-10', str_contains($id, 'FS-10') && str_contains($en, 'FS-10'));
check('soft bridge FS-12', str_contains($id, 'FS-12') && str_contains($en, 'FS-12'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no php artisan serve', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('mentions Serial later', str_contains($id, 'Serial Monitor') && str_contains($en, 'Serial Monitor'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
