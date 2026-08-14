<?php

/** Static quality gate for Article #106 / FS-36. Run: php scripts/audit-article106.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article106Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs36-assets.py');

$seeder = new Database\Seeders\Article106Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check106(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems106(string $html): int
{
    $chunk = explode('id="fsiot-sd-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check106('draft status', str_contains($source, "'status' => 'draft'"));
check106('null publication date', str_contains($source, "'published_at' => null"));
check106('expected slug', str_contains($source, 'fullstack-iot-esp32-microsd-log-csv'));
check106('route and controller exist', str_contains($routes, 'seed-article-106-draft') && str_contains($controller, 'seedArticle106Draft'));
check106('priority deploy and seed exist', str_contains($workflow, 'id: curl106_priority') && str_contains($workflow, 'seed-article-106-draft'));
check106('priority upload precedes FS-35 uploads', strpos($workflow, 'id: curl106_priority') < strpos($workflow, 'id: curl105_priority'));
check106('FS-36 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl106_priority.conclusion == 'success'"));
check106('late FS-36 seed is required after FTP', str_contains($workflow, 'Seed article 106 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-esp32-microsd-log-csv'));
check106('kit photos are in the priority upload', str_contains($workflow, 'fs36-serial-monitor.png') && str_contains($workflow, 'fs36-modul-kit.png') && str_contains($workflow, 'kit-microsd-card.jpg') && str_contains($workflow, 'kit-microsd-spi.jpg') && str_contains($workflow, 'kit-dht22.jpg'));
check106('cover is copied into public storage', str_contains($source, 'articles/covers/fs36-cover-sd') && str_contains($source, "Storage::disk('public')->put"));
check106('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check106('ID and EN references', str_contains($body, '#106 (ini)') && str_contains($bodyEn, '#106 (this article)'));
check106('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check106('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka File Explorer') && str_contains($body, 'Buka Arduino IDE') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open File Explorer') && str_contains($bodyEn, 'Open Arduino IDE'));
check106('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'Upload, lalu buka Serial Monitor') && str_contains($bodyEn, 'Upload, then open Serial Monitor'));
check106('tools are named before sketch', strpos($body, 'Buka File Explorer') < strpos($body, '#include &lt;SD.h&gt;') && str_contains($body, 'Buka dulu File Explorer') && str_contains($body, 'Buka dulu Arduino IDE'));
check106('SD.h is described as ESP32 core not UNO Library Manager', str_contains($body, 'sudah termasuk core') && str_contains($body, 'Jangan</strong> memasang library bernama SD') && str_contains($bodyEn, 'already ship in the Arduino-ESP32 core') && str_contains($bodyEn, 'Do not</strong> install an SD library'));
check106('DHT library path is the book icon', str_contains($body, 'ikon tiga buku') && str_contains($body, 'satu-satunya jalur yang dipakai hari ini') && str_contains($body, 'Jangan memakai menu lama') && ! str_contains($body, 'Sketch → Include Library'));
check106('Serial Monitor path is Tools menu', str_contains($body, 'Tools → Serial Monitor') && str_contains($bodyEn, 'Tools → Serial Monitor') && str_contains($body, 'bukan</strong> Library Manager'));
check106('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check106('Arduino licence is cited', str_contains($body, 'Creative Commons Attribution-Share Alike 4.0') && str_contains($bodyEn, 'Creative Commons Attribution-Share Alike 4.0'));
check106('card photo is cited and not wired to ESP32', str_contains($body, 'kit-microsd-card.jpg') && str_contains($body, '2015_Karta_microSD_z_adapterem_SD.jpg') && str_contains($body, 'Jangan menyambungkannya ke pin ESP32') && str_contains($bodyEn, 'Do not wire it to ESP32 pins'));
check106('SPI photo is cited and pin-order copying is forbidden', str_contains($body, 'kit-microsd-spi.jpg') && str_contains($body, 'SD_Card_Breakout_Board.jpg') && str_contains($body, 'Jangan menyalin urutan kaki dari foto') && str_contains($bodyEn, 'Do not copy pin order from the photo'));
check106('DHT22 photo is cited', str_contains($body, 'kit-dht22.jpg') && str_contains($body, 'DHT_22_Sensor.jpg'));
check106('locked VSPI pins are explicit', str_contains($body, 'GPIO 5') && str_contains($body, 'GPIO 18') && str_contains($body, 'GPIO 19') && str_contains($body, 'GPIO 23') && str_contains($source, 'CS_PIN = 5') && str_contains($source, 'SPI.begin(18, 19, 23, CS_PIN)'));
check106('pin guessing is forbidden', str_contains($body, 'Jangan menebak pin') && str_contains($bodyEn, 'Do not guess pins'));
check106('no AC mains in the lab', str_contains($body, 'Jangan colok AC 220V') && str_contains($bodyEn, 'Do not connect AC mains'));
check106('MQTT is explicitly unused today', str_contains($body, 'MQTTX') && str_contains($body, 'Mosquitto') && str_contains($body, '<strong>tidak</strong> dipakai') && str_contains($bodyEn, 'are <strong>not</strong> used'));
check106('relay is explicitly unused today', str_contains($body, 'Relay GPIO 26 <strong>tidak</strong> dipakai hari ini') && str_contains($bodyEn, 'relay is <strong>not</strong> used today'));
check106('FAT32 is required', str_contains($body, 'FAT32') && str_contains($bodyEn, 'FAT32') && str_contains($body, 'NTFS') && str_contains($body, 'exFAT'));
check106('macOS and Linux open a GUI first', str_contains($body, 'buka aplikasi <strong>Disk Utility</strong> dulu') && str_contains($body, 'buka aplikasi <strong>Disks</strong> dulu') && str_contains($bodyEn, 'open the <strong>Disk Utility</strong> app first') && str_contains($bodyEn, 'open the <strong>Disks</strong> app first'));
check106('sketch writes log.csv with millis default', str_contains($body, 'LOG_PATH[] = &quot;/log.csv&quot;') && str_contains($body, 'PAKAI_NTP = false') && str_contains($body, 'timestamp_ms,temperature_c') && str_contains($body, 'FILE_APPEND'));
check106('NTP uses configTime after Wi-Fi', str_contains($body, 'configTime(7 * 3600') && str_contains($body, 'pool.ntp.org') && str_contains($body, 'Jam dinding WIB siap.') && str_contains($body, 'GANTI_NAMA_WIFI'));
check106('failed mount message is concrete', str_contains($body, 'Kartu tidak terbaca. Periksa CS=GPIO 5, format FAT32, dan GND bersama.'));
check106('official sources are cited', str_contains($body, 'docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html') && str_contains($body, 'docs.arduino.cc/libraries/sd/') && str_contains($body, 'adafruit/DHT-sensor-library') && str_contains($body, 'system_time.html') && str_contains($body, 'ntppool.org'));
check106('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check106('cover uses the public FS-36 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs36-cover-sd.webp'));
check106('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check106('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check106('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check106('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check106('eight FS-36 image figures in both languages', substr_count($body, '/images/fsiot/fs36-') === 8 && substr_count($bodyEn, '/images/fsiot/fs36-') === 8);
check106('wiring figure includes SD module GND', str_contains($body, 'GND modul SD juga ke GND ESP32') && str_contains($bodyEn, 'SD module GND also goes to ESP32 GND'));
check106('six-pin kit module is illustrated before the Adafruit photo', strpos($body, 'fs36-modul-kit.png') < strpos($body, 'kit-microsd-spi.jpg') && strpos($bodyEn, 'fs36-modul-kit.png') < strpos($bodyEn, 'kit-microsd-spi.jpg'));
check106('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-36)') === 5 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-36)') === 5);
check106('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check106('interactive checklist is wired', str_contains($body, 'id="fsiot-sd-checklist"') && str_contains($body, 'id="fsiot-sd-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-106'") && str_contains($langId, 'fsiot_sd_badge') && str_contains($langEn, 'fsiot_sd_badge'));
check106('ten checklist items match in both languages', checklistItems106($body) === 10 && checklistItems106($bodyEn) === 10);
check106('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-sd-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-sd-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check106('next module is FS-37', str_contains($body, 'FS-37') && str_contains($bodyEn, 'FS-37'));
check106('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check106('EYD uses suhu not English telemetry in ID', ! str_contains($body, 'telemetry') && str_contains($body, 'suhu'));
check106('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check106('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check106('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check106('deploy hook needles for FS-36', str_contains($controller, "'Kartu siap. Menulis /log.csv'") && str_contains($controller, "'PAKAI_NTP'") && str_contains($controller, "'FAT32'") && str_contains($controller, "'FS-37'"));
check106('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
preg_match("/'fsiot_sd_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_sd_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check106('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));

foreach ([
    'fs36-cover-sd.jpg',
    'fs36-cover-sd.webp',
    'fs36-tools-order.png',
    'fs36-format-fat32.png',
    'fs36-modul-kit.png',
    'fs36-wiring-spi.png',
    'fs36-csv-flow.png',
    'fs36-millis-vs-ntp.png',
    'fs36-serial-monitor.png',
    'fs36-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check106($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$formatSize = getimagesize($root.'/public/images/fsiot/fs36-format-fat32.png');
check106('FAT32 illustration is cropped to a readable height', $formatSize !== false && $formatSize[1] <= 800);
$serialSize = getimagesize($root.'/public/images/fsiot/fs36-serial-monitor.png');
check106('Serial Monitor illustration is cropped to a readable height', $serialSize !== false && $serialSize[1] <= 800);
$cardSize = getimagesize($root.'/public/images/fsiot/kit-microsd-card.jpg');
check106('kit-microsd-card.jpg exists and is readable', $cardSize !== false && $cardSize[0] >= 400);
$spiSize = getimagesize($root.'/public/images/fsiot/kit-microsd-spi.jpg');
check106('kit-microsd-spi.jpg exists and is readable', $spiSize !== false && $spiSize[0] >= 400);
$dhtSize = getimagesize($root.'/public/images/fsiot/kit-dht22.jpg');
check106('kit-dht22.jpg exists and is readable', $dhtSize !== false && $dhtSize[0] >= 400);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
