<?php

/**
 * Local audit for Article73Seeder (FS-03) — pre-launch draft.
 * Run: php scripts/audit-article73.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article73Seeder;

$ref = new ReflectionClass(Article73Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__.'/../database/seeders/Article73Seeder.php');

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;
    echo ($ok ? 'OK    ' : 'FAIL  ').$label.PHP_EOL;
    $ok ? $pass++ : $fail++;
}

check('status draft', str_contains($src, "'status'") && str_contains($src, "'draft'"));
check('published_at null', str_contains($src, "'published_at'") && str_contains($src, 'null'));
check('slug fullstack-iot-kamus-mini', str_contains($src, 'fullstack-iot-kamus-mini'));
check('category iot-smart-device', str_contains($src, 'iot-smart-device'));
check('tag fullstack-iot', str_contains($src, "'fullstack-iot'"));
check('title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('no publish hook yet', ! str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-73'));
check('SEO no Awam/Beginner stamp', ! str_contains($src, 'untuk Awam') && ! str_contains($src, 'Beginner Mini'));

check('ID self-ref #73 (ini)', str_contains($id, '#73 (ini)'));
check('EN self-ref #73 (this article)', str_contains($en, '#73 (this article)'));
check('no Awam stamp ID', ! str_contains($id, 'Awam:') && ! str_contains($id, 'Awam —'));
check('no Beginner stamp EN', ! str_contains($en, 'Beginner:') && ! str_contains($en, 'Beginner —'));
check('no awam word in body ID', ! preg_match('/\bawam\b/i', $id));
check('no beginner word in body EN', ! preg_match('/\bbeginner\b/i', $en));
check('friendly tip labels ID', str_contains($id, 'Intinya:') && str_contains($id, 'Cara pakai artikel ini') && str_contains($id, 'Analogi:'));
check('friendly tip labels EN', str_contains($en, 'In short:') && str_contains($en, 'How to use this article') && str_contains($en, 'Analogy:'));
check('H2 parity', substr_count($id, '<h2') === substr_count($en, '<h2'));
check('figures both (>=3: sense + photos + flow)', substr_count($id, '<figure') >= 3 && substr_count($en, '<figure') >= 3);
check('flow diagram telemetry/command dir', str_contains($id, 'perangkat → sistem') && str_contains($id, 'sistem → perangkat') && str_contains($en, 'device → system') && str_contains($en, 'system → device'));
check('example photos + cites', str_contains($id, 'kit-dht22.jpg') && str_contains($id, 'kit-led-5mm.jpg') && str_contains($id, 'esp32-devkitc-overview.jpg') && str_contains($id, 'DHT_22_Sensor.jpg'));
check('EN example photos', str_contains($en, 'kit-dht22.jpg') && str_contains($en, 'kit-led-5mm.jpg'));
check('photo files exist', is_file(__DIR__.'/../public/images/fsiot/kit-dht22.jpg') && is_file(__DIR__.'/../public/images/fsiot/kit-led-5mm.jpg') && is_file(__DIR__.'/../public/images/fsiot/esp32-devkitc-overview.jpg'));
check('Arti sederhana / Plain meaning', str_contains($id, 'Arti sederhana') && str_contains($en, 'Plain meaning'));
check('ID mistakes heading', str_contains($id, 'Kesalahan yang sering terjadi'));
check('EN mistakes heading', str_contains($en, 'Common mistakes'));
check('EYD perangkat lunak', str_contains($id, 'perangkat lunak'));
check('EYD praktik', (bool) preg_match('/\bpraktik\b/u', $id) && ! preg_match('/\bpraktek\b/u', $id));

foreach (['Sensor', 'Aktuator', 'Mikrokontroler', 'Firmware', 'GPIO', 'Sketch', 'Upload', 'Serial', 'Telemetry', 'Command', 'Broker', 'Topic', 'API', 'SQLite', 'OTA', 'Flask', 'Node-RED', 'NTP'] as $t) {
    check('ID has '.$t, str_contains($id, $t) || ($t === 'Aktuator' && str_contains($id, 'aktuator')));
}
foreach (['Sensor', 'Actuator', 'Microcontroller', 'Firmware', 'GPIO', 'Sketch', 'Upload', 'Serial', 'Telemetry', 'Command', 'Broker', 'Topic', 'API', 'SQLite', 'OTA', 'Flask', 'Node-RED', 'NTP'] as $t) {
    check('EN has '.$t, str_contains($en, $t));
}

check('ID Persiapan + no-syntax', str_contains($id, 'Persiapan') && str_contains($id, 'Tidak ada perintah sintaks hari ini'));
check('EN Preparation + no-syntax', str_contains($en, 'Preparation') && str_contains($en, 'There is no syntax to run today'));
check('quiz 15 + key', str_contains($id, '15') && str_contains($id, '1B') && str_contains($en, '1B'));
check('quiz interactive markers', str_contains($id, 'id="fsiot-kuis-matching"') && str_contains($id, 'id="fsiot-kuis-kunci"') && str_contains($en, 'id="fsiot-kuis-matching"'));
check('quiz mentions interactive', str_contains($id, 'kuis interaktif') && str_contains($en, 'interactive quiz'));
check('score target 12/15', str_contains($id, '12/15') && str_contains($en, '12/15'));
check('DevKitC-1', str_contains($id, 'ESP32-DevKitC-1') && str_contains($en, 'ESP32-DevKitC-1'));
check('soft FS-02', str_contains($id, 'FS-02') && str_contains($en, 'FS-02'));
check('soft FS-04', str_contains($id, 'FS-04') && str_contains($en, 'FS-04'));
check('jalur link', str_contains($id, '/belajar/fullstack-iot') && str_contains($en, '/belajar/fullstack-iot'));
check('kesalahan >= 5', substr_count($id, '<li><strong>') >= 5);
check('no hardlink artikel FS', ! preg_match('#/artikel/fullstack-iot-[a-z]#', $id.$en));
check('no Seri ESP32 prereq', ! preg_match('#/artikel/(esp32|arduino)#i', $id.$en));
check('no pre blocks', ! str_contains($id, '<pre') && ! str_contains($en, '<pre'));
check('no Soft bridge jargon', ! str_contains($id, 'Soft bridge') && ! str_contains($en, 'soft bridge'));

$enPlain = strip_tags($en);
foreach (['Pendahuluan', 'Persiapan', 'Kesalahan yang sering terjadi', 'Kesimpulan', 'Awam:', 'Intinya:'] as $w) {
    check('No residual Indo in EN: '.$w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>'.$w));
}

echo PHP_EOL."{$pass} pass / {$fail} fail".PHP_EOL;
exit($fail > 0 ? 1 : 0);
