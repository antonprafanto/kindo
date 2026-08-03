<?php

/**
 * Local audit for Article90Seeder (FS-20) — pre-launch draft.
 * Run: php scripts/audit-article90.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article90Seeder;

$ref = new ReflectionClass(Article90Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article90Seeder.php');
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
check('slug fullstack-iot-pwm-redupkan-led', str_contains($src, 'fullstack-iot-pwm-redupkan-led'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('seo without Awam/Beginner', ! str_contains($src, 'Awam') && ! str_contains($src, 'Beginner for'));
check('no publish hook yet', ! str_contains($routes, 'publish-article-90'));
check('seed route exists', str_contains($routes, 'seed-article-90-draft'));

check('ID self-ref #90 (ini)', str_contains($id, '#90 (ini)'));
check('EN self-ref #90 (this article)', str_contains($en, '#90 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 7', substr_count($id, '<figure') >= 7 && substr_count($en, '<figure') >= 7);
check('main breadboard PNG asset', str_contains($id, 'fs18-led-gpio2-breadboard.png') && is_file(__DIR__.'/../public/images/fsiot/fs18-led-gpio2-breadboard.png'));
check('duty cycle examples PNG', str_contains($id, 'fs20-duty-cycle-examples.png') && is_file(__DIR__.'/../public/images/fsiot/fs20-duty-cycle-examples.png'));
check('pwm 5steps PNG', str_contains($id, 'fs20-pwm-5steps.png') && is_file(__DIR__.'/../public/images/fsiot/fs20-pwm-5steps.png'));
check('Gambar utama label ID', str_contains($id, 'Gambar utama'));
check('Main figure label EN', str_contains($en, 'Main figure'));
check('cover asset exists', is_file(__DIR__.'/../public/images/fsiot/fs20-cover-pwm-led.jpg'));
check('cover set-if-blank in seeder', str_contains($src, 'fs20-cover-pwm-led.jpg') && str_contains($src, 'cover_image'));

check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('tools-first Upload IDE', str_contains($id, 'Tidak perlu hari ini') && str_contains($en, 'Not needed today') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload ID', str_contains($id, 'Cara menguji perintah di atas') && str_contains($id, 'Arduino IDE'));
check('how to test IDE Upload EN', str_contains($en, 'How to test the commands above') && str_contains($en, 'Arduino IDE'));

check('IDE asset', str_contains($id, 'fs11-ide-overview-cite.png') && is_file(__DIR__.'/../public/images/fsiot/fs11-ide-overview-cite.png'));
check('board overview', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('kit LED asset', str_contains($id, 'kit-led-5mm.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-led-5mm.jpg'));
check('Arduino analogWrite cite', str_contains($id, 'functions/analog-io/analogwrite') && str_contains($en, 'functions/analog-io/analogwrite'));
check('Espressif LEDC cite', str_contains($id, 'api/ledc.html') && str_contains($en, 'api/ledc.html'));
check('KI diagram cite', str_contains($id, 'buatan Koding Indonesia') && str_contains($en, 'diagram by Koding Indonesia'));
check('Commons duty cycle cite', str_contains($id, 'commons.wikimedia.org/wiki/File:Duty_Cycle_Examples.png') && str_contains($en, 'commons.wikimedia.org/wiki/File:Duty_Cycle_Examples.png'));
check('Commons pwm 5steps cite', str_contains($id, 'commons.wikimedia.org/wiki/File:Pwm_5steps.gif') && str_contains($en, 'commons.wikimedia.org/wiki/File:Pwm_5steps.gif'));

check('sketch FS20_led_fade', str_contains($id, 'FS20_led_fade') && str_contains($en, 'FS20_led_fade'));
check('analogWrite', str_contains($id, 'analogWrite') && str_contains($en, 'analogWrite'));
check('duty cycle', str_contains($id, 'duty cycle') && str_contains($en, 'duty cycle'));
check('GPIO 2 LED', str_contains($id, 'GPIO 2') && str_contains($en, 'GPIO 2'));
check('BUILDER soft via FS-18', str_contains($id, 'FS-18') && str_contains($en, 'FS-18'));

preg_match_all('/<svg[\s\S]*?<\/svg>/', $id, $svgIdBlocks);
preg_match_all('/<svg[\s\S]*?<\/svg>/', $en, $svgEnBlocks);
$noStrongInSvg = array_reduce($svgIdBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true)
    && array_reduce($svgEnBlocks[0] ?? [], fn (bool $c, string $s): bool => $c && ! str_contains($s, '<strong>'), true);
check('no strong tags inside SVG', $noStrongInSvg);
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist ul id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-pwm-checklist-items"'));
check('checklist h2 id survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-pwm-checklist"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-pwm-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-pwm-checklist-items'), '<li>') >= 10);
check('interactive pwm checklist wired', str_contains($show, 'initFsiotPwmChecklist') && str_contains($show, 'fsiot-pwm-checklist'));
check('pwm checklist lang ID', str_contains(file_get_contents(__DIR__.'/../lang/id/ui.php'), 'fsiot_pwm_badge'));
check('pwm checklist lang EN', str_contains(file_get_contents(__DIR__.'/../lang/en/ui.php'), 'fsiot_pwm_badge'));

check('soft bridge FS-21', str_contains($id, 'FS-21') && str_contains($en, 'FS-21'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Intinya:', 'Analogi:', 'Cara pakai'] as $indo) {
    check('No residual Indo in EN: '.$indo, ! str_contains($enPlain, $indo));
}

echo PHP_EOL."$pass pass / $fail fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
