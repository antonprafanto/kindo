<?php

/** Static quality gate for Article #107 / FS-37. Run: php scripts/audit-article107.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article107Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs37-assets.py');

$seeder = new Database\Seeders\Article107Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check107(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems107(string $html): int
{
    $chunk = explode('id="fsiot-forward-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check107('draft status', str_contains($source, "'status' => 'draft'"));
check107('null publication date', str_contains($source, "'published_at' => null"));
check107('expected slug', str_contains($source, 'fullstack-iot-esp32-sd-store-and-forward'));
check107('route and controller exist', str_contains($routes, 'seed-article-107-draft') && str_contains($controller, 'seedArticle107Draft'));
check107('priority deploy and seed exist', str_contains($workflow, 'id: curl107_priority') && str_contains($workflow, 'seed-article-107-draft'));
check107('priority upload precedes FS-36 uploads', strpos($workflow, 'id: curl107_priority') < strpos($workflow, 'id: curl106_priority'));
check107('FS-37 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl107_priority.conclusion == 'success'"));
check107('late FS-37 seed is required after FTP', str_contains($workflow, 'Seed article 107 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-esp32-sd-store-and-forward'));
check107('FS-37 images are in the priority upload', str_contains($workflow, 'fs37-serial-monitor.png') && str_contains($workflow, 'fs37-mqttx-backfill.png') && str_contains($workflow, 'fs37-two-wifi.png') && str_contains($workflow, 'fs37-hotspot-demo.png') && str_contains($workflow, 'kit-microsd-card.jpg') && str_contains($workflow, 'kit-microsd-spi.jpg') && str_contains($workflow, 'kit-dht22.jpg') && str_contains($workflow, 'fs36-wiring-spi.png') && str_contains($workflow, 'fs36-modul-kit.png'));
check107('cover is copied into public storage', str_contains($source, 'articles/covers/fs37-cover-forward') && str_contains($source, "Storage::disk('public')->put"));
check107('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check107('ID and EN references', str_contains($body, '#107 (ini)') && str_contains($bodyEn, '#107 (this article)'));
check107('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check107('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka MQTTX') && str_contains($body, 'Buka Arduino IDE') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open MQTTX') && str_contains($bodyEn, 'Open Arduino IDE'));
check107('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'Upload, Serial Monitor, lalu buka panel HP') && str_contains($bodyEn, 'Upload, Serial Monitor, then open the phone panel'));
check107('tools are named before sketch', strpos($body, 'Buka browser') < strpos($body, '#include &lt;SD.h&gt;') && str_contains($body, 'Buka dulu Arduino IDE') && str_contains($body, 'Buka dulu PowerShell') && str_contains($body, 'Buka dulu panel atas HP'));
check107('SD.h is described as ESP32 core not UNO Library Manager', str_contains($body, 'sudah termasuk core') && str_contains($body, 'Jangan</strong> memasang library') && str_contains($bodyEn, 'already ship in the core') && str_contains($bodyEn, 'Do not</strong> install an SD library'));
check107('DHT and MQTT library path is the book icon', str_contains($body, 'ikon tiga buku') && str_contains($body, 'satu-satunya jalur yang dipakai hari ini') && str_contains($body, 'Jangan memakai menu lama') && ! str_contains($body, 'Sketch → Include Library'));
check107('Serial Monitor path is Tools menu', str_contains($body, 'Tools → Serial Monitor') && str_contains($bodyEn, 'Tools → Serial Monitor') && str_contains($body, 'bukan</strong> Library Manager'));
check107('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check107('Arduino licence is cited', str_contains($body, 'Creative Commons Attribution-Share Alike 4.0') && str_contains($bodyEn, 'Creative Commons Attribution-Share Alike 4.0'));
check107('card photo is cited and not wired to ESP32', str_contains($body, 'kit-microsd-card.jpg') && str_contains($body, '2015_Karta_microSD_z_adapterem_SD.jpg') && str_contains($body, 'Jangan menyambungkannya ke pin ESP32') && str_contains($bodyEn, 'Do not wire it to ESP32 pins'));
check107('SPI photo is cited and pin-order copying is forbidden', str_contains($body, 'kit-microsd-spi.jpg') && str_contains($body, 'SD_Card_Breakout_Board.jpg') && str_contains($body, 'Jangan menyalin urutan kaki dari foto') && str_contains($bodyEn, 'Do not copy pin order from the photo'));
check107('six-pin kit module is illustrated before the Adafruit photo', strpos($body, 'fs36-modul-kit.png') < strpos($body, 'kit-microsd-spi.jpg') && strpos($bodyEn, 'fs36-modul-kit.png') < strpos($bodyEn, 'kit-microsd-spi.jpg'));
check107('DHT22 photo is cited', str_contains($body, 'kit-dht22.jpg') && str_contains($body, 'DHT_22_Sensor.jpg'));
check107('locked VSPI pins are explicit', str_contains($body, 'GPIO 5') && str_contains($body, 'GPIO 18') && str_contains($body, 'GPIO 19') && str_contains($body, 'GPIO 23') && str_contains($source, 'CS_PIN = 5') && str_contains($source, 'SPI.begin(18, 19, 23, CS_PIN)'));
check107('pin guessing is forbidden', str_contains($body, 'Jangan menebak pin') && str_contains($bodyEn, 'Do not guess pins'));
check107('no AC mains in the lab', str_contains($body, 'Jangan colok AC 220V') && str_contains($bodyEn, 'Do not connect AC mains'));
check107('relay is explicitly unused today', str_contains($body, 'Relay GPIO 26 <strong>tidak</strong> dipakai hari ini') && str_contains($bodyEn, 'relay is <strong>not</strong> used today'));
check107('home router must stay on', str_contains($body, 'bukan router rumah') && str_contains($body, 'Jangan matikan router rumah') && str_contains($bodyEn, 'not the home router') && str_contains($bodyEn, 'Do not turn off the home router'));
check107('ESP32 uses phone hotspot credentials', str_contains($source, 'GANTI_NAMA_HOTSPOT') && str_contains($source, 'GANTI_SANDI_HOTSPOT') && str_contains($body, 'hotspot HP'));
check107('MQTT host is PC IPv4 not localhost', str_contains($body, '127.0.0.1') && str_contains($body, 'IPv4 PC') && str_contains($source, 'MQTT_HOST[] = "192.168.1.23"') && str_contains($body, 'mosquitto-fs34.conf'));
check107('MQTTX connection name is set', str_contains($body, 'FS37 store-forward LAN') && str_contains($bodyEn, 'FS37 store-forward LAN'));
check107('queue lives on pending.csv not RAM', str_contains($source, 'PENDING_PATH[] = "/pending.csv"') && str_contains($source, 'MAX_FLUSH_PER_LOOP = 5') && str_contains($body, 'bukan RAM tak terbatas') && str_contains($bodyEn, 'not an unbounded RAM array'));
check107('JSON marks live vs backfill', str_contains($source, 'from_sd') && str_contains($body, 'from_sd: true') && str_contains($body, 'from_sd: false'));
check107('Serial needles exist', str_contains($source, 'Kartu siap. Antrian di /pending.csv') && str_contains($source, 'Antrian hanya di kartu, bukan RAM tak terbatas.') && str_contains($source, 'MQTT tersambung. Mengirim antrian kartu.') && str_contains($source, 'Terkirim:') && str_contains($source, 'Wi-Fi putus. Disimpan ke pending.csv') && str_contains($source, 'Kirim ulang dari kartu:') && str_contains($source, 'Kartu tidak terbaca. Periksa CS=GPIO 5, format FAT32, dan GND bersama.'));
check107('Serial illustration shows JSON backfill', str_contains($assets, 'Kirim ulang dari kartu: {"from_sd":true'));
check107('Terkirim is Serial text not MQTTX', ! str_contains($body, 'Terkirim:</code> di MQTTX') && ! str_contains($bodyEn, 'appears in MQTTX'));
check107('tools caption opens the phone panel', str_contains($body, 'lalu buka panel HP') && str_contains($bodyEn, 'then open the phone panel'));
check107('failed mount message is concrete', str_contains($body, 'Kartu tidak terbaca. Periksa CS=GPIO 5, format FAT32, dan GND bersama.'));
check107('FAT32 is required', str_contains($body, 'FAT32') && str_contains($bodyEn, 'FAT32'));
check107('official sources are cited', str_contains($body, 'docs.espressif.com/projects/arduino-esp32/en/latest/api/sd.html') && str_contains($body, 'github.com/arduino-libraries/ArduinoMqttClient') && str_contains($body, 'arduinojson.org') && str_contains($body, 'adafruit/DHT-sensor-library') && str_contains($body, 'mqttx.app') && str_contains($body, 'mosquitto.org/man/mosquitto-conf-5.html'));
check107('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check107('cover uses the public FS-37 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs37-cover-forward.webp'));
check107('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check107('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check107('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check107('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check107('nine FS-37 image figures in both languages', substr_count($body, '/images/fsiot/fs37-') === 9 && substr_count($bodyEn, '/images/fsiot/fs37-') === 9);
check107('reused FS-36 wiring and kit figures', str_contains($body, 'fs36-wiring-spi.png') && str_contains($body, 'fs36-modul-kit.png') && str_contains($bodyEn, 'fs36-wiring-spi.png') && str_contains($bodyEn, 'fs36-modul-kit.png'));
check107('two-Wi-Fi figure replaces same-network LAN diagram', str_contains($body, 'fs37-two-wifi.png') && str_contains($bodyEn, 'fs37-two-wifi.png') && ! str_contains($body, 'fs34-lan-address.png'));
check107('hotspot demo figure exists', str_contains($body, 'fs37-hotspot-demo.png') && str_contains($bodyEn, 'fs37-hotspot-demo.png'));
check107('wiring figure includes SD module GND', str_contains($body, 'GND modul SD juga ke GND ESP32') && str_contains($bodyEn, 'SD module GND also goes to ESP32 GND'));
check107('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-37)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-37)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-37)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-37)') === 2);
check107('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check107('interactive checklist is wired', str_contains($body, 'id="fsiot-forward-checklist"') && str_contains($body, 'id="fsiot-forward-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-107'") && str_contains($langId, 'fsiot_forward_badge') && str_contains($langEn, 'fsiot_forward_badge'));
check107('ten checklist items match in both languages', checklistItems107($body) === 10 && checklistItems107($bodyEn) === 10);
check107('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-forward-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-forward-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check107('next module is FS-38', str_contains($body, 'FS-38') && str_contains($bodyEn, 'FS-38') && str_contains($body, 'Node-RED'));
check107('Python and Node-RED are deferred', str_contains($body, 'Node-RED dan Python ditunda') && str_contains($bodyEn, 'Node-RED and Python wait'));
check107('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check107('ID uses suhu for the measured value', str_contains($body, 'suhu'));
check107('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check107('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check107('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check107('deploy hook needles for FS-37', str_contains($controller, "'Kartu siap. Antrian di /pending.csv'") && str_contains($controller, "'Kirim ulang dari kartu:'") && str_contains($controller, "'from_sd'") && str_contains($controller, "'FS-38'"));
check107('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check107('sketch name is FS37_sd_store_forward', str_contains($body, 'FS37_sd_store_forward') && str_contains($bodyEn, 'FS37_sd_store_forward'));
preg_match("/'fsiot_forward_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_forward_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check107('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_forward_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check107('pass checklist copy mentions the card queue', str_contains($passId[1] ?? '', 'antrian kartu') || str_contains($passId[1] ?? '', 'pending.csv'));

foreach ([
    'fs37-cover-forward.jpg',
    'fs37-cover-forward.webp',
    'fs37-tools-order.png',
    'fs37-offline-online.png',
    'fs37-ram-vs-sd.png',
    'fs37-pending-csv.png',
    'fs37-serial-monitor.png',
    'fs37-mqttx-backfill.png',
    'fs37-troubleshooting.png',
    'fs37-two-wifi.png',
    'fs37-hotspot-demo.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check107($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$serialSize = getimagesize($root.'/public/images/fsiot/fs37-serial-monitor.png');
check107('Serial Monitor illustration is cropped to a readable height', $serialSize !== false && $serialSize[1] <= 800);
$mqttxSize = getimagesize($root.'/public/images/fsiot/fs37-mqttx-backfill.png');
check107('MQTTX illustration is cropped to a readable height', $mqttxSize !== false && $mqttxSize[1] <= 800);
$cardSize = getimagesize($root.'/public/images/fsiot/kit-microsd-card.jpg');
check107('kit-microsd-card.jpg exists and is readable', $cardSize !== false && $cardSize[0] >= 400);
$spiSize = getimagesize($root.'/public/images/fsiot/kit-microsd-spi.jpg');
check107('kit-microsd-spi.jpg exists and is readable', $spiSize !== false && $spiSize[0] >= 400);
$kitSize = getimagesize($root.'/public/images/fsiot/fs36-modul-kit.png');
check107('fs36-modul-kit.png exists and is readable', $kitSize !== false && $kitSize[0] >= 1000);
$dhtSize = getimagesize($root.'/public/images/fsiot/kit-dht22.jpg');
check107('kit-dht22.jpg exists and is readable', $dhtSize !== false && $dhtSize[0] >= 400);
$wiringSize = getimagesize($root.'/public/images/fsiot/fs36-wiring-spi.png');
check107('fs36-wiring-spi.png exists and is readable', $wiringSize !== false && $wiringSize[0] >= 1000);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
