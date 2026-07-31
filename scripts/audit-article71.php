<?php

/**
 * Local audit for Article71Seeder (FS-01) — pre-launch draft.
 * Run: php scripts/audit-article71.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article71Seeder;

$ref = new ReflectionClass(Article71Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article71Seeder.php');

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
check('slug fullstack-iot-apa-itu-iot', str_contains($src, 'fullstack-iot-apa-itu-iot'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! file_exists(__DIR__.'/../app/Http/Controllers/DeployController.php') || ! str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-71'));

check('ID self-ref #71 (ini)', str_contains($id, '#71 (ini)'));
check('EN self-ref #71 (this article)', str_contains($en, '#71 (this article)'));
check('ID Awam >= 5', substr_count($id, 'Awam:') + substr_count($id, 'Awam —') >= 5);
check('EN Beginner >= 5', substr_count($en, 'Beginner:') + substr_count($en, 'Beginner —') >= 5);
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figure both >= 5', substr_count($id, '<figure') >= 5 && substr_count($en, '<figure') >= 5);

check('ID IoT definition themes', str_contains($id, 'benda fisik') && str_contains($id, 'Wi‑Fi'));
check('ID remote vs IoT', str_contains($id, 'Remote') || str_contains($id, 'remote'));
check('ID Stasiun Ruang Belajar', str_contains($id, 'Stasiun Ruang Belajar'));
check('EN Study Room Station', str_contains($en, 'Study Room Station'));
check('ID DevKitC-1 mention', str_contains($id, 'ESP32-DevKitC-1'));
check('EN DevKitC-1 mention', str_contains($en, 'ESP32-DevKitC-1'));
check('ID Persiapan / browser', str_contains($id, 'Persiapan') && str_contains($id, 'Browser'));
check('EN Preparation / Browser', str_contains($en, 'Preparation') && str_contains($en, 'Browser'));
check('ID no-syntax day message', str_contains($id, 'Tidak ada perintah sintaks hari ini'));
check('EN no-syntax day message', str_contains($en, 'There is no syntax to run today'));
check('ID cara pakai urutan', str_contains($id, 'cara pakai artikel ini'));
check('EN how to use order', str_contains($en, 'how to use this article'));
check('ID open notes first', str_contains($id, 'buka alat ini dulu') && str_contains($id, 'catatan'));
check('EN open notes first', str_contains($en, 'open this tool first') && str_contains($en, 'notes'));
check('ID board overview image', str_contains($id, 'esp32-devkitc-overview.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('EN board overview image', str_contains($en, 'esp32-devkitc-overview.jpg'));
check('ID board legend table', str_contains($id, 'Arti awam') && str_contains($id, 'USB-to-UART Bridge') && str_contains($id, 'Micro-USB atau USB-C'));
check('EN board legend table', str_contains($en, 'Beginner meaning') && str_contains($en, 'Micro-USB or USB-C'));
check('ID Espressif citation', str_contains($id, 'Espressif Systems') && str_contains($id, 'esp32-devkitc/user_guide.html'));
check('EN Espressif citation', str_contains($en, 'Espressif Systems') && str_contains($en, 'esp32-devkitc/user_guide.html'));
check('ID remote photo file', str_contains($id, 'kit-tv-remote.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-tv-remote.jpg'));
check('ID remote Commons cite', str_contains($id, 'LG_IR_TV_Remote_Control_AKB74595401.jpg'));
check('ID smart bulbs photo', str_contains($id, 'kit-smart-bulbs.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-smart-bulbs.jpg'));
check('ID smart bulbs cite', str_contains($id, 'LIFX_bulbs.jpg'));
check('ID smart plugs photo', str_contains($id, 'kit-smart-plugs.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-smart-plugs.jpg'));
check('ID smart plugs cite', str_contains($id, 'Wemo_smart_plugs_and_switches.jpg'));
check('ID jalur page link', str_contains($id, '/belajar/fullstack-iot'));
check('EN jalur page link', str_contains($en, '/belajar/fullstack-iot'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('ID no Arduino IDE install', ! str_contains($id, 'Arduino IDE') || str_contains($id, 'belum') || str_contains($id, 'Tidak perlu'));
check('ID latihan 3 contoh', str_contains($id, '3 contoh') || str_contains($id, 'tiga'));
check('EN practice 3 examples', str_contains($en, '3 IoT examples') || str_contains($en, 'three things'));
check('ID checklist markers', str_contains($id, 'id="fsiot-iot-checklist"') && str_contains($id, 'id="fsiot-iot-checklist-items"'));
check('EN checklist markers', str_contains($en, 'id="fsiot-iot-checklist-items"'));
check('checklist survives sanitizer', str_contains(app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($id), 'id="fsiot-iot-checklist-items"'));
check('ID kesalahan >= 7 items', substr_count($id, '<li><strong>') >= 7);
check('EYD kelembapan', str_contains($id, 'kelembapan'));
check('EYD pemantauan', str_contains($id, 'pemantauan'));
check('no hardlink to #72 article', ! str_contains($id, '/artikel/fullstack-iot') && ! str_contains($en, '/artikel/fullstack-iot'));
check('no Seri ESP32 prerequisite link', ! preg_match('#/artikel/(esp32|arduino)#i', $id.$en));
check('no wiring/code blocks', ! str_contains($id, '<pre') && ! str_contains($en, '<pre'));
check('soft bridge FS-02 text', str_contains($id, 'FS-02') && str_contains($en, 'FS-02'));
check('remote SVG title ID', str_contains($id, 'Remote lokal vs arah IoT'));
check('station SVG no overflow label', str_contains($id, 'dashboard</text>') || str_contains($id, '>dashboard<'));
$enPlain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $en) ?? '');
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan umum', 'Kesimpulan', 'Awam:'] as $w) {
    check('No residual Indo prose in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL."{$pass} pass / {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
