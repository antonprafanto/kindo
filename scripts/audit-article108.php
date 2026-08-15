<?php

/** Static quality gate for Article #108 / FS-38. Run: php scripts/audit-article108.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article108Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs38-assets.py');

$seeder = new Database\Seeders\Article108Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);
$sketchMethod = $reflection->getMethod('sketch');
$sketchMethod->setAccessible(true);
$sketch = $sketchMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check108(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems108(string $html): int
{
    $chunk = explode('id="fsiot-rules-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check108('draft status', str_contains($source, "'status' => 'draft'"));
check108('null publication date', str_contains($source, "'published_at' => null"));
check108('expected slug', str_contains($source, 'fullstack-iot-pc-rules-nodered-mqtt'));
check108('route and controller exist', str_contains($routes, 'seed-article-108-draft') && str_contains($controller, 'seedArticle108Draft'));
check108('priority deploy and seed exist', str_contains($workflow, 'id: curl108_priority') && str_contains($workflow, 'seed-article-108-draft'));
check108('priority upload precedes FS-37 uploads', strpos($workflow, 'id: curl108_priority') < strpos($workflow, 'id: curl107_priority'));
check108('FS-38 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl108_priority.conclusion == 'success'"));
check108('late FS-38 seed is required after FTP', str_contains($workflow, 'Seed article 108 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-pc-rules-nodered-mqtt'));
check108('FS-38 images are in the priority upload', str_contains($workflow, 'fs38-nodered-editor.png') && str_contains($workflow, 'fs38-threshold-deploy.png') && str_contains($workflow, 'fs38-mqttx-manual.png') && str_contains($workflow, 'fs38-same-wifi.png') && str_contains($workflow, 'fs38-brain-on-pc.png') && str_contains($workflow, 'kit-relay-5v.jpg') && str_contains($workflow, 'kit-dht22.jpg'));
check108('cover is copied into public storage', str_contains($source, 'articles/covers/fs38-cover-rules') && str_contains($source, "Storage::disk('public')->put"));
check108('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check108('ID and EN references', str_contains($body, '#108 (ini)') && str_contains($bodyEn, '#108 (this article)'));
check108('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check108('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka Arduino IDE') && str_contains($body, 'MQTTX') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open Arduino IDE') && str_contains($bodyEn, 'MQTTX'));
check108('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'Upload sekali; ambang diubah di Node-RED') && str_contains($bodyEn, 'Upload once; change the threshold in Node-RED'));
check108('tools are named before sketch', strpos($body, 'Buka browser') < strpos($body, '#include &lt;WiFi.h&gt;') && str_contains($body, 'Buka dulu Arduino IDE') && str_contains($body, 'Buka dulu PowerShell') && str_contains($body, 'Buka dulu browser'));
check108('Node.js LTS is installed from nodejs.org before npm', str_contains($body, 'nodejs.org') && str_contains($body, 'LTS') && strpos($body, 'nodejs.org') < strpos($body, 'npm install -g node-red') && str_contains($bodyEn, 'nodejs.org'));
check108('Node-RED Windows docs are cited', str_contains($body, 'nodered.org/docs/getting-started/windows') && str_contains($bodyEn, 'nodered.org/docs/getting-started/windows') && str_contains($body, 'Apache License 2.0'));
check108('Windows install does not use unsafe-perm', str_contains($body, 'npm install -g node-red') && ! str_contains($body, '--unsafe-perm') && ! str_contains($bodyEn, '--unsafe-perm'));
check108('Library Manager path is the book icon', str_contains($body, 'ikon tiga buku') && str_contains($body, 'satu-satunya jalur yang dipakai hari ini') && str_contains($body, 'Jangan memakai menu lama') && ! str_contains($body, 'Sketch → Include Library'));
check108('Serial Monitor path is Tools menu', str_contains($body, 'Tools → Serial Monitor') && str_contains($bodyEn, 'Tools → Serial Monitor') && str_contains($body, 'bukan</strong> Library Manager'));
check108('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check108('Arduino licence is cited', str_contains($body, 'Creative Commons Attribution-Share Alike 4.0') && str_contains($bodyEn, 'Creative Commons Attribution-Share Alike 4.0'));
check108('relay photo is cited and pin-order copying is forbidden', str_contains($body, 'kit-relay-5v.jpg') && str_contains($body, 'SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg') && str_contains($body, 'Jangan menyalin urutan kaki dari foto') && str_contains($bodyEn, 'Do not copy pin order from the photo'));
check108('DHT22 photo is cited', str_contains($body, 'kit-dht22.jpg') && str_contains($body, 'DHT_22_Sensor.jpg'));
check108('locked pins are explicit', str_contains($body, 'GPIO 4') && str_contains($body, 'GPIO 26') && str_contains($source, 'DHT_PIN = 4') && str_contains($source, 'RELAY_PIN = 26'));
check108('pin guessing is forbidden', str_contains($body, 'Jangan menebak pin') && str_contains($bodyEn, 'Do not guess pins'));
check108('no AC mains in the lab', str_contains($body, 'Jangan colok AC 220V') && str_contains($bodyEn, 'Do not connect AC mains') && str_contains($body, 'NC/COM/NO'));
check108('home Wi-Fi is used not the FS-37 hotspot demo', str_contains($body, 'Wi-Fi rumah yang sama') && str_contains($body, 'hotspot FS-37') && str_contains($bodyEn, 'same home Wi-Fi') && str_contains($source, 'GANTI_NAMA_WIFI') && ! str_contains($source, 'GANTI_NAMA_HOTSPOT'));
check108('MQTT host is PC IPv4 not localhost on the ESP32', str_contains($body, '127.0.0.1') && str_contains($body, 'IPv4 PC') && str_contains($source, 'MQTT_HOST[] = "192.168.1.23"') && str_contains($body, 'mosquitto-fs34.conf') && str_contains($body, 'Node-RED menyambung ke broker di <code>127.0.0.1</code>'));
check108('MQTTX connection name is set', str_contains($body, 'FS38 rules LAN') && str_contains($bodyEn, 'FS38 rules LAN'));
check108('command JSON is explicit', str_contains($body, '{"device_id":"esp32-meja-01","relay":"on"}') && str_contains($body, '"relay":"off"') && str_contains($bodyEn, '{"device_id":"esp32-meja-01","relay":"on"}'));
check108('topics match telemetry command status', str_contains($source, 'kodingindonesia/fsiot/esp32-meja-01/telemetry') && str_contains($source, 'kodingindonesia/fsiot/esp32-meja-01/command') && str_contains($source, 'kodingindonesia/fsiot/esp32-meja-01/status'));
check108('firmware has no temperature threshold', str_contains($source, 'FS38_device_only') && ! str_contains($sketch, '> 30') && ! preg_match('/temperature\s*[><]=?/', $sketch) && str_contains($body, 'if (suhu &gt; 30)'));
check108('Serial needles exist', str_contains($source, 'Ambang ada di PC, bukan di sketch.') && str_contains($source, 'MQTT tersambung.') && str_contains($source, 'Subscribe command siap.') && str_contains($source, 'Terkirim:') && str_contains($source, 'Perintah:') && str_contains($source, 'Relay ON') && str_contains($source, 'Relay OFF') && str_contains($source, 'DHT22 belum terbaca. Periksa kabel GPIO 4.'));
check108('editor illustration includes mqtt out on the canvas', str_contains($assets, 'mqtt out\\ncommand') && str_contains($body, 'mqtt out'));
check108('Deploy is top right not Arduino top left', str_contains($assets, 'kanan atas') && ! str_contains($assets, 'kiri atas') && str_contains($body, 'Tombol Deploy di <strong>kanan atas</strong>') && str_contains($body, 'bukan kiri atas seperti Upload Arduino') && str_contains($bodyEn, 'top right') && str_contains($bodyEn, 'not the top left like Arduino Upload'));
check108('failed DHT message is concrete', str_contains($body, 'DHT22 belum terbaca. Periksa kabel GPIO 4.'));
check108('official sources are cited', str_contains($body, 'github.com/arduino-libraries/ArduinoMqttClient') && str_contains($body, 'arduinojson.org') && str_contains($body, 'adafruit/DHT-sensor-library') && str_contains($body, 'mqttx.app') && str_contains($body, 'mosquitto.org/man/mosquitto-conf-5.html') && str_contains($body, 'nodered.org'));
check108('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check108('cover uses the public FS-38 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs38-cover-rules.webp'));
check108('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check108('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check108('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check108('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check108('nine FS-38 image figures in both languages', substr_count($body, '/images/fsiot/fs38-') === 9 && substr_count($bodyEn, '/images/fsiot/fs38-') === 9);
check108('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-38)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-38)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-38)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-38)') === 2);
check108('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check108('interactive checklist is wired', str_contains($body, 'id="fsiot-rules-checklist"') && str_contains($body, 'id="fsiot-rules-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-108'") && str_contains($blade, 'initFsiotRulesChecklist') && str_contains($langId, 'fsiot_rules_badge') && str_contains($langEn, 'fsiot_rules_badge'));
check108('ten checklist items match in both languages', checklistItems108($body) === 10 && checklistItems108($bodyEn) === 10);
check108('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-rules-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-rules-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check108('next module is FS-39 Python', str_contains($body, 'FS-39') && str_contains($bodyEn, 'FS-39') && str_contains($body, 'Python terpasang dari nol') && str_contains($bodyEn, 'Python is installed from scratch'));
check108('Python is deferred not taught', str_contains($body, 'Python ditunda ke FS-39') && str_contains($bodyEn, 'Python waits for FS-39') && str_contains($body, 'jangan ketik Python') && str_contains($bodyEn, 'Do not type Python'));
check108('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check108('ID uses suhu for the measured value', str_contains($body, 'suhu'));
check108('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check108('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check108('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check108('deploy hook needles for FS-38', str_contains($controller, "'#108 (ini)'") && str_contains($controller, "'Ambang ada di PC, bukan di sketch.'") && str_contains($controller, "'FS38_device_only'") && str_contains($controller, "'FS-39'"));
check108('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check108('sketch name is FS38_device_only', str_contains($body, 'FS38_device_only') && str_contains($bodyEn, 'FS38_device_only'));
preg_match("/'fsiot_rules_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_rules_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check108('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_rules_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check108('pass checklist copy mentions Node-RED on the PC', str_contains($passId[1] ?? '', 'Node-RED') && str_contains($passId[1] ?? '', 'sketch'));

foreach ([
    'fs38-cover-rules.jpg',
    'fs38-cover-rules.webp',
    'fs38-tools-order.png',
    'fs38-same-wifi.png',
    'fs38-brain-on-pc.png',
    'fs38-flow.png',
    'fs38-wiring.png',
    'fs38-nodered-editor.png',
    'fs38-threshold-deploy.png',
    'fs38-mqttx-manual.png',
    'fs38-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check108($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$editorSize = getimagesize($root.'/public/images/fsiot/fs38-nodered-editor.png');
check108('Node-RED editor illustration is cropped to a readable height', $editorSize !== false && $editorSize[1] <= 800);
$mqttxSize = getimagesize($root.'/public/images/fsiot/fs38-mqttx-manual.png');
check108('MQTTX illustration is cropped to a readable height', $mqttxSize !== false && $mqttxSize[1] <= 800);
$relaySize = getimagesize($root.'/public/images/fsiot/kit-relay-5v.jpg');
check108('kit-relay-5v.jpg exists and is readable', $relaySize !== false && $relaySize[0] >= 400);
$dhtSize = getimagesize($root.'/public/images/fsiot/kit-dht22.jpg');
check108('kit-dht22.jpg exists and is readable', $dhtSize !== false && $dhtSize[0] >= 400);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
