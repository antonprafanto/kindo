<?php

/**
 * Local audit for Article74Seeder (FS-04) — pre-launch draft.
 * Run: php scripts/audit-article74.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article74Seeder;

$ref = new ReflectionClass(Article74Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article74Seeder.php');
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
check('slug fullstack-iot-buka-kotak-kit', str_contains($src, 'fullstack-iot-buka-kotak-kit'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains($routes, 'publish-article-74'));
check('SEO no belanja awam / beginner shopping', ! str_contains($src, 'belanja awam') && ! str_contains($src, 'beginner shopping'));
check('SEO shopping guide', str_contains($src, 'panduan belanja') && str_contains($src, 'shopping guide'));

check('ID self-ref #74 (ini)', str_contains($id, '#74 (ini)'));
check('EN self-ref #74 (this article)', str_contains($en, '#74 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both >= 4', substr_count($id, '<figure') >= 4 && substr_count($en, '<figure') >= 4);
check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('shopping heading ID', str_contains($id, 'Belanja bertahap'));
check('shopping heading EN', str_contains($en, 'Shopping in stages'));

check('ID Persiapan + no-syntax', str_contains($id, 'Persiapan') && str_contains($id, 'Tidak ada perintah sintaks hari ini'));
check('EN Preparation + no-syntax', str_contains($en, 'Preparation') && str_contains($en, 'There is no syntax to run today'));
check('ID tools-first browser checklist', str_contains($id, 'Browser') && str_contains($id, 'checklist interaktif'));
check('EN tools-first browser checklist', str_contains($en, 'Browser') && str_contains($en, 'interactive checklist'));

check('DevKitC-1 both', str_contains($id, 'ESP32-DevKitC-1') && str_contains($en, 'ESP32-DevKitC-1'));
check('overview image + Espressif cite', str_contains($id, 'esp32-devkitc-overview.jpg') && str_contains($id, 'Espressif Systems'));
check('pinout image + cite', str_contains($id, 'esp32-devkitc-1-pinlayout.jpg') && str_contains($id, 'ESP32-DevKitC-1.html'));
check('EN overview + pinout', str_contains($en, 'esp32-devkitc-overview.jpg') && str_contains($en, 'esp32-devkitc-1-pinlayout.jpg'));

$kitFiles = [
    'kit-breadboard.jpg',
    'kit-jumper-wires.jpg',
    'kit-led-5mm.jpg',
    'kit-resistor.jpg',
    'kit-tactile-button.jpg',
    'kit-dht22.jpg',
    'kit-ldr.jpg',
    'kit-relay-5v.jpg',
    'kit-multimeter.jpg',
];
foreach ($kitFiles as $kf) {
    check("ID kit photo {$kf}", str_contains($id, $kf));
    check("EN kit photo {$kf}", str_contains($en, $kf));
}
check('ID Commons citation', str_contains($id, 'commons.wikimedia.org') && str_contains($id, 'CC BY-SA'));
check('EN Commons citation', str_contains($en, 'commons.wikimedia.org') && str_contains($en, 'CC BY-SA'));
check('kit photo files on disk', collect($kitFiles)->every(fn ($f) => is_file(__DIR__.'/../public/images/fsiot/'.$f)));

check('breadboard content', str_contains($id, 'Breadboard') && str_contains($en, 'Breadboard'));
check('LED circle body (sanitizer-safe)', str_contains($id, '<circle') && str_contains($en, '<circle'));
check('LED polarity labels clear', str_contains($id, 'kaki panjang') && str_contains($id, 'kaki pendek') && str_contains($en, 'long leg'));
check('breadboard caption no typo', str_contains($id, 'Diagram dalaman breadboard') && ! str_contains($id, 'Inganan'));
check('breadboard shows trench', str_contains($id, 'parit') && str_contains($en, 'trench'));
check('no section-number jargon §', ! str_contains($id, '§') && ! str_contains($en, '§'));
check('no silkscreen jargon', ! str_contains($id, 'silkscreen') && ! str_contains($en, 'silkscreen'));
check('USB data vs charge', str_contains($id, 'charge-only') || str_contains($id, 'Charge-only'));
check('EN USB charge-only', str_contains($en, 'charge-only') || str_contains($en, 'Charge-only'));
check('DHT22 + DHT11 temp OK', str_contains($id, 'DHT22') && str_contains($id, 'DHT11'));
check('EYD kelembapan', str_contains($id, 'kelembapan'));
check('no AC 220V encouragement', str_contains($id, 'AC 220V') && str_contains($id, 'Jangan'));
check('price table IDR', str_contains($id, 'Rp ') && str_contains($en, 'Rp '));

check('checklist markers', str_contains($id, 'id="fsiot-kit-checklist"') && str_contains($id, 'id="fsiot-kit-checklist-items"'));
check('EN checklist markers', str_contains($en, 'id="fsiot-kit-checklist"') && str_contains($en, 'id="fsiot-kit-checklist-items"'));
check('checklist has 12 items ID', substr_count(strstr($id, 'fsiot-kit-checklist-items'), '<li>') >= 12);
check('checklist has 12 items EN', substr_count(strstr($en, 'fsiot-kit-checklist-items'), '<li>') >= 12);

check('soft mention FS-03', str_contains($id, 'FS-03') && str_contains($en, 'FS-03'));
check('soft bridge FS-05', str_contains($id, 'FS-05') && str_contains($en, 'FS-05'));
check('jalur page link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink FS articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $id.$en));
check('no Seri ESP32 prereq link', ! preg_match('#/artikel/(esp32|arduino)#i', $id.$en));
check('no pre blocks', ! str_contains($id, '<pre') && ! str_contains($en, '<pre'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));
check('EN BOOT + EN buttons', str_contains($en, 'BOOT') && str_contains($en, 'EN'));
check('ID tombol EN BOOT', str_contains($id, 'EN') && str_contains($id, 'BOOT'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL."{$pass} pass / {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
