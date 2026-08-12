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

check104('draft status', str_contains($source, "'status' => 'draft'"));
check104('expected slug', str_contains($source, 'fullstack-iot-esp32-dht22-mqtt-json-telemetry'));
check104('route and controller exist', str_contains($routes, 'seed-article-104-draft') && str_contains($controller, 'seedArticle104Draft'));
check104('priority deploy and seed exist', str_contains($workflow, 'id: curl104_priority') && str_contains($workflow, 'seed-article-104-draft'));
check104('priority upload precedes FS-33 uploads', strpos($workflow, 'id: curl104_priority') < strpos($workflow, 'id: curl103_priority'));
check104('ID and EN references', str_contains($body, '#104 (ini)') && str_contains($bodyEn, '#104 (this article)'));
check104('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2'));
check104('five figures in both languages', substr_count($body, '<figure') === 5 && substr_count($bodyEn, '<figure') === 5);
check104('tools are named before sketch', strpos($body, 'Buka Arduino IDE') < strpos($body, '#include &lt;WiFi.h&gt;') && str_contains($body, 'Buka PowerShell') && str_contains($body, 'Buka MQTTX paling akhir'));
check104('DHT22 wiring is explicit', str_contains($body, 'VCC → 3V3') && str_contains($body, 'DATA → GPIO 4') && str_contains($body, 'GND → GND'));
check104('LAN boundary is explicit', str_contains($body, 'IPv4 PC dari <code>ipconfig</code>') && str_contains($body, '<code>127.0.0.1</code>') && str_contains($body, 'guest Wi-Fi'));
check104('no router or public broker instruction', str_contains($body, 'Jangan membuka port router') && str_contains($body, 'broker publik') && str_contains($body, 'Private networks'));
check104('temporary listener is scoped', str_contains($body, 'listener_allow_anonymous true') && str_contains($body, 'lab LAN singkat') && str_contains($body, 'Ctrl+C'));
check104('sketch uses supported MQTT and JSON pattern', str_contains($body, '#include &lt;ArduinoMqttClient.h&gt;') && str_contains($body, 'JsonDocument data') && str_contains($body, 'serializeJson(data, payload)'));
check104('sketch identifies device and telemetry topic', str_contains($body, 'const char DEVICE_ID[]') && str_contains($body, 'kodingindonesia/fsiot/esp32-meja-01/telemetry'));
check104('sketch uses millis for publish and reconnects', str_contains($body, 'PUBLISH_INTERVAL_MS = 5000UL') && str_contains($body, 'mqttClient.poll()') && str_contains($body, 'lastWifiAttemptAt') && str_contains($body, 'lastMqttAttemptAt'));
check104('no real secret placeholder', str_contains($body, 'GANTI_NAMA_WIFI') && str_contains($body, 'GANTI_SANDI_WIFI'));
check104('official sources are cited', str_contains($body, 'mosquitto.org/man/mosquitto-conf-5.html') && str_contains($body, 'docs.espressif.com') && str_contains($body, 'arduino-libraries/ArduinoMqttClient') && str_contains($body, 'arduinojson.org') && str_contains($body, 'adafruit/DHT-sensor-library'));
check104('interactive checklist is wired', str_contains($body, 'id="fsiot-telemetry-checklist"') && str_contains($body, 'id="fsiot-telemetry-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-104'") && str_contains($langId, 'fsiot_telemetry_badge') && str_contains($langEn, 'fsiot_telemetry_badge'));
check104('ten checklist items in both languages', substr_count(explode('id="fsiot-telemetry-checklist-items"', $body)[1] ?? '', '<li>') >= 10 && substr_count(explode('id="fsiot-telemetry-checklist-items"', $bodyEn)[1] ?? '', '<li>') >= 10);
check104('next module is FS-35', str_contains($body, 'FS-35') && str_contains($bodyEn, 'FS-35'));
check104('cover uses the public FS-34 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs34-cover-telemetry.webp'));

foreach (['fs34-cover-telemetry.jpg', 'fs34-cover-telemetry.webp', 'fs34-tools-order.png', 'fs34-wiring-dht22.png', 'fs34-lan-address.png', 'fs34-json-flow.png', 'fs34-troubleshooting.png'] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check104($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
