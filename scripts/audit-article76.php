<?php

/**
 * Local audit for Article76Seeder (FS-06) — pre-launch draft.
 * Run: php scripts/audit-article76.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article76Seeder;

$ref = new ReflectionClass(Article76Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article76Seeder.php');
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
check('slug fullstack-iot-komputer-siap-driver-arduino-ide', str_contains($src, 'fullstack-iot-komputer-siap-driver-arduino-ide'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains($routes, 'publish-article-76'));
check('seed route exists', str_contains($routes, 'seed-article-76-draft'));

check('ID self-ref #76 (ini)', str_contains($id, '#76 (ini)'));
check('EN self-ref #76 (this article)', str_contains($en, '#76 (this article)'));
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
check('tools-first no PHP today', str_contains($id, 'Tidak perlu hari ini') && str_contains($id, 'Arduino IDE') && str_contains($en, 'Not needed today'));
check('soft bridge FS-07 plain', str_contains($id, 'multimeter dasar') && str_contains($en, 'basic multimeter'));

check('ID Persiapan tools-first', str_contains($id, 'Persiapan') && str_contains($id, 'Arduino IDE'));
check('EN Preparation tools-first', str_contains($en, 'Preparation') && str_contains($en, 'Arduino IDE'));
check('ID Device Manager', str_contains($id, 'Device Manager') || str_contains($id, 'devmgmt.msc'));
check('EN Device Manager', str_contains($en, 'Device Manager') || str_contains($en, 'devmgmt.msc'));
check('has sketch setup/loop ID', str_contains($id, 'void setup') && str_contains($id, 'void loop'));
check('has sketch setup/loop EN', str_contains($en, 'void setup') && str_contains($en, 'void loop'));
check('Done uploading both', str_contains($id, 'Done uploading') && str_contains($en, 'Done uploading'));
check('Board Manager URL', str_contains($id, 'package_esp32_index.json') && str_contains($en, 'package_esp32_index.json'));
check('ESP32 Dev Module', str_contains($id, 'ESP32 Dev Module') && str_contains($en, 'ESP32 Dev Module'));
check('CP210x driver link', str_contains($id, 'silabs.com') && str_contains($en, 'silabs.com'));
check('CH340 driver link', str_contains($id, 'wch-ic.com') && str_contains($en, 'wch-ic.com'));
check('charge-only mention', str_contains($id, 'charge-only') && str_contains($en, 'charge-only'));
check('overview image + Espressif', str_contains($id, 'esp32-devkitc-overview.jpg') && str_contains($id, 'Espressif') && str_contains($id, 'esp-dev-kits'));
check('SVG workflow + chip', str_contains($id, 'Alur hari ini') && str_contains($id, 'CP2102'));
check('device manager photo', str_contains($id, 'fs06-device-manager-esp32.png') && str_contains($id, 'establish-serial-connection'));
check('arduino ide board port photo', str_contains($id, 'fs06-arduino-ide-overview.png') && str_contains($id, 'Select Board'));
check('device manager photo on disk', is_file(__DIR__.'/../public/images/fsiot/fs06-device-manager-esp32.png'));
check('arduino ide overview on disk', is_file(__DIR__.'/../public/images/fsiot/fs06-arduino-ide-overview.png'));
check('overview eager load', str_contains($id, 'loading="eager"') && str_contains($en, 'loading="eager"'));
check('no tspan in SVG', ! str_contains($id, '<tspan') && ! str_contains($en, '<tspan'));

check('checklist markers', str_contains($id, 'id="fsiot-setup-checklist"') && str_contains($id, 'id="fsiot-setup-checklist-items"'));
check('EN checklist markers', str_contains($en, 'id="fsiot-setup-checklist"') && str_contains($en, 'id="fsiot-setup-checklist-items"'));
check('checklist has 10 items ID', substr_count(strstr($id, 'fsiot-setup-checklist-items'), '<li>') >= 10);
check('checklist has 10 items EN', substr_count(strstr($en, 'fsiot-setup-checklist-items'), '<li>') >= 10);

check('soft mention FS-05', str_contains($id, 'FS-05') && str_contains($en, 'FS-05'));
check('soft bridge FS-07', str_contains($id, 'FS-07') && str_contains($en, 'FS-07'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no Seri ESP32 prereq link', ! preg_match('#/artikel/(esp32|arduino)#i', $id.$en));
check('no php artisan in practice', ! str_contains($id, 'php artisan serve') && ! str_contains($en, 'php artisan serve'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('no Laragon as main tool', ! str_contains($id, 'Laragon') || str_contains($id, 'belum Laragon') || str_contains($id, 'Tidak perlu hari ini'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL.$pass.' pass / '.$fail.' fail'.PHP_EOL;
exit($fail > 0 ? 1 : 0);
