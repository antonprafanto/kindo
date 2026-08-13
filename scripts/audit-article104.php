<?php

/** Static quality gate for Article #104 / FS-34. Run: php scripts/audit-article104.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article104Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');

$seeder = new Database\Seeders\Article104Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check104(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems104(string $html): int
{
    $chunk = explode('id="fsiot-telemetry-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check104('draft status', str_contains($source, "'status' => 'draft'"));
check104('null publication date', str_contains($source, "'published_at' => null"));
check104('expected slug', str_contains($source, 'fullstack-iot-esp32-dht22-mqtt-json-telemetry'));
check104('route and controller exist', str_contains($routes, 'seed-article-104-draft') && str_contains($controller, 'seedArticle104Draft'));
check104('priority deploy and seed exist', str_contains($workflow, 'id: curl104_priority') && str_contains($workflow, 'seed-article-104-draft'));
check104('priority upload precedes FS-33 uploads', strpos($workflow, 'id: curl104_priority') < strpos($workflow, 'id: curl103_priority'));
check104('FS-34 seed is enabled after priority upload', str_contains($workflow, "if: steps.curl104_priority.outcome == 'success'"));
check104('library figure is in the priority upload', str_contains($workflow, 'fs34-library-manager.png') && str_contains($workflow, 'kit-dht22.jpg'));
check104('cover is copied into public storage', str_contains($source, 'articles/covers/fs34-cover-telemetry') && str_contains($source, "Storage::disk('public')->put"));
check104('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check104('ID and EN references', str_contains($body, '#104 (ini)') && str_contains($bodyEn, '#104 (this article)'));
check104('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check104('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka Arduino IDE') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open Arduino IDE'));
check104('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'Buka MQTTX setelah broker berjalan') && str_contains($bodyEn, 'Open MQTTX after the broker is running'));
check104('tools are named before sketch', strpos($body, 'Buka Arduino IDE') < strpos($body, '#include &lt;WiFi.h&gt;') && str_contains($body, 'Buka dulu PowerShell') && str_contains($body, 'Buka MQTTX setelah broker berjalan'));
check104('Arduino IDE library path is current and visual guidance is present', str_contains($body, 'Library Manager</strong>') && str_contains($body, 'ikon tiga buku') && str_contains($body, 'Jangan memakai menu lama'));
check104('old Tools Manage Libraries menu is only mentioned as forbidden', str_contains($body, 'Tools → Manage Libraries') && str_contains($body, 'Jangan memakai menu lama'));
check104('Library Manager uses a KI illustration not a dimmed official screenshot', str_contains($body, 'Ini tampilan yang benar, bukan layar error') && str_contains($bodyEn, 'This is the correct view, not an error screen') && str_contains($body, 'Screenshot jendela resmi tidak dipakai utuh') && str_contains($bodyEn, 'official window screenshot is not used as-is'));
check104('Arduino licence is cited', str_contains($body, 'Creative Commons Attribution-Share Alike 4.0') && str_contains($bodyEn, 'Creative Commons Attribution-Share Alike 4.0'));
check104('UNO is explained as a docs example only', str_contains($body, 'Dokumentasi resmi Arduino kadang menampilkan UNO') && str_contains($bodyEn, 'Official Arduino documentation sometimes shows an UNO'));
check104('DHT22 photo is cited and pin-order copying is forbidden', str_contains($body, 'kit-dht22.jpg') && str_contains($body, 'commons.wikimedia.org/wiki/File:DHT_22_Sensor.jpg') && str_contains($body, 'Jangan menyalin urutan kaki dari foto') && str_contains($bodyEn, 'Do not copy pin order from the photo'));
check104('DHT22 wiring is explicit', str_contains($body, 'VCC → 3V3') && str_contains($body, 'DATA → GPIO 4') && str_contains($body, 'GND → GND'));
check104('pin guessing is forbidden', str_contains($body, 'Jangan menebak pin') && str_contains($bodyEn, 'Do not guess pins'));
check104('LAN boundary is explicit', str_contains($body, 'IPv4 PC dari <code>ipconfig</code>') && str_contains($body, '<code>127.0.0.1</code>') && str_contains($body, 'guest Wi-Fi'));
check104('PowerShell paste is explained', str_contains($body, 'Cara menempel perintah') && str_contains($bodyEn, 'How to paste'));
check104('expected broker output is explained', str_contains($body, 'terlihat angka <code>1883</code>') && str_contains($bodyEn, 'shows port <code>1883</code>'));
check104('keep broker window open', str_contains($body, 'Biarkan jendela ini terbuka') && str_contains($bodyEn, 'Keep it open'));
check104('Connect is allowed after broker runs', str_contains($body, 'Baru sekarang klik <em>New Connection</em>') && str_contains($bodyEn, 'Only now click <em>New Connection</em>'));
check104('MQTTX host is PC IPv4 not loopback', str_contains($body, 'bukan <code>127.0.0.1</code>') && str_contains($bodyEn, 'not <code>127.0.0.1</code>'));
check104('no router or public broker instruction', str_contains($body, 'Jangan membuka port router') && str_contains($body, 'broker publik') && str_contains($body, 'Private networks'));
check104('public broker is defined in plain language', str_contains($body, 'broker di internet milik pihak lain') && str_contains($bodyEn, 'broker on the internet owned by someone else'));
check104('temporary listener is scoped', str_contains($body, 'listener_allow_anonymous true') && str_contains($body, 'lab LAN singkat') && str_contains($body, 'Ctrl+C'));
check104('FS-33 listener contrast is explained', str_contains($body, 'FS-33 tidak memakainya') && str_contains($bodyEn, 'FS-33 did not use one'));
check104('macOS and Linux open Terminal first', str_contains($body, 'buka aplikasi <strong>Terminal</strong> dulu') && str_contains($bodyEn, 'open the <strong>Terminal</strong> app first'));
check104('sketch uses supported MQTT and JSON pattern', str_contains($body, '#include &lt;ArduinoMqttClient.h&gt;') && str_contains($body, 'JsonDocument data') && str_contains($body, 'serializeJson(data, payload)'));
check104('sketch identifies device and telemetry topic', str_contains($body, 'const char DEVICE_ID[]') && str_contains($body, 'kodingindonesia/fsiot/esp32-meja-01/telemetry'));
check104('sketch uses millis for publish and reconnects', str_contains($body, 'PUBLISH_INTERVAL_MS = 5000UL') && str_contains($body, 'mqttClient.poll()') && str_contains($body, 'lastWifiAttemptAt') && str_contains($body, 'lastMqttAttemptAt'));
check104('expected Serial success text', str_contains($body, 'MQTT tersambung.') && str_contains($body, 'Terkirim:'));
check104('no real secret placeholder', str_contains($body, 'GANTI_NAMA_WIFI') && str_contains($body, 'GANTI_SANDI_WIFI'));
check104('official sources are cited', str_contains($body, 'mosquitto.org/man/mosquitto-conf-5.html') && str_contains($body, 'docs.espressif.com') && str_contains($body, 'arduino-libraries/ArduinoMqttClient') && str_contains($body, 'arduinojson.org') && str_contains($body, 'adafruit/DHT-sensor-library'));
check104('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check104('cover uses the public FS-34 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs34-cover-telemetry.webp'));
check104('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check104('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check104('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check104('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check104('seven FS-34 image figures in both languages', substr_count($body, '/images/fsiot/fs34-') === 7 && substr_count($bodyEn, '/images/fsiot/fs34-') === 7);
check104('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-34)') === 5 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-34)') === 5);
check104('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check104('interactive checklist is wired', str_contains($body, 'id="fsiot-telemetry-checklist"') && str_contains($body, 'id="fsiot-telemetry-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-104'") && str_contains($langId, 'fsiot_telemetry_badge') && str_contains($langEn, 'fsiot_telemetry_badge'));
check104('ten checklist items match in both languages', checklistItems104($body) === 10 && checklistItems104($bodyEn) === 10);
check104('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-telemetry-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-telemetry-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check104('next module is FS-35', str_contains($body, 'FS-35') && str_contains($bodyEn, 'FS-35'));
check104('EYD avoids sungguhan', ! str_contains($body, 'sungguhan') && str_contains($body, 'Pesan yang sama harus muncul'));
check104('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check104('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check104('deploy hook needles for FS-34', str_contains($controller, "'ArduinoMqttClient'") && str_contains($controller, "'listener_allow_anonymous'") && str_contains($controller, "'FS-35'"));
check104('MQTTX JSON illustration is cited and not an official public-broker screenshot', str_contains($body, 'fs34-mqttx-json.png') && str_contains($body, 'mqttx.app') && str_contains($body, 'Apache License 2.0') && str_contains($workflow, 'fs34-mqttx-json.png'));
check104('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check104('Library Manager path is a single beginner instruction', str_contains($body, 'satu-satunya jalur yang dipakai hari ini') && str_contains($bodyEn, 'only path used today') && ! str_contains($body, 'Sketch → Include Library'));

foreach ([
    'fs34-cover-telemetry.jpg',
    'fs34-cover-telemetry.webp',
    'fs34-tools-order.png',
    'fs34-library-manager.png',
    'fs34-wiring-dht22.png',
    'fs34-lan-address.png',
    'fs34-json-flow.png',
    'fs34-mqttx-json.png',
    'fs34-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check104($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$librarySize = getimagesize($root.'/public/images/fsiot/fs34-library-manager.png');
check104('library illustration is cropped to a readable height', $librarySize !== false && $librarySize[1] <= 800);
$kitSize = getimagesize($root.'/public/images/fsiot/kit-dht22.jpg');
check104('kit-dht22.jpg exists and is readable', $kitSize !== false && $kitSize[0] >= 400);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
