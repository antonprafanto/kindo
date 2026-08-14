<?php

/** Static quality gate for Article #105 / FS-35. Run: php scripts/audit-article105.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article105Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');

$seeder = new Database\Seeders\Article105Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check105(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems105(string $html): int
{
    $chunk = explode('id="fsiot-command-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check105('draft status', str_contains($source, "'status' => 'draft'"));
check105('null publication date', str_contains($source, "'published_at' => null"));
check105('expected slug', str_contains($source, 'fullstack-iot-esp32-mqtt-command-relay'));
check105('route and controller exist', str_contains($routes, 'seed-article-105-draft') && str_contains($controller, 'seedArticle105Draft'));
check105('priority deploy and seed exist', str_contains($workflow, 'id: curl105_priority') && str_contains($workflow, 'seed-article-105-draft'));
check105('priority upload precedes FS-34 uploads', strpos($workflow, 'id: curl105_priority') < strpos($workflow, 'id: curl104_priority'));
check105('FS-35 seed is enabled after priority upload', str_contains($workflow, "if: steps.curl105_priority.outcome == 'success'"));
check105('relay photo is in the priority upload', str_contains($workflow, 'fs35-mqttx-publish.png') && str_contains($workflow, 'kit-relay-5v.jpg') && str_contains($workflow, 'fs35-library-manager.png'));
check105('cover is copied into public storage', str_contains($source, 'articles/covers/fs35-cover-command') && str_contains($source, "Storage::disk('public')->put"));
check105('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check105('ID and EN references', str_contains($body, '#105 (ini)') && str_contains($bodyEn, '#105 (this article)'));
check105('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check105('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka Arduino IDE') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open Arduino IDE'));
check105('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'Buka MQTTX setelah broker berjalan') && str_contains($bodyEn, 'Open MQTTX after the broker is running'));
check105('tools are named before sketch', strpos($body, 'Buka Arduino IDE') < strpos($body, '#include &lt;WiFi.h&gt;') && str_contains($body, 'Buka dulu PowerShell') && str_contains($body, 'Buka MQTTX setelah broker berjalan'));
check105('Arduino IDE library path is a single beginner instruction', str_contains($body, 'ikon tiga buku') && str_contains($body, 'satu-satunya jalur yang dipakai hari ini') && str_contains($body, 'Jangan memakai menu lama') && ! str_contains($body, 'Sketch → Include Library'));
check105('Library Manager describes the book-icon step without saying error', str_contains($body, 'Klik ikon tiga buku di bilah kiri') && str_contains($bodyEn, 'Click the three-book icon in the left bar') && str_contains($body, 'Screenshot jendela resmi tidak dipakai utuh') && str_contains($bodyEn, 'official window screenshot is not used as-is') && ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen'));
check105('MQTTX command state describes Host/JSON without saying error', str_contains($body, 'MQTTX sudah siap mengirim perintah ke ESP32') && str_contains($bodyEn, 'MQTTX is ready to send a command to the ESP32') && ! str_contains($body, 'bukan layar error') && ! str_contains($body, 'bukan tombol silang') && ! str_contains($bodyEn, 'not an error screen') && ! str_contains($bodyEn, 'not a close button'));
check105('Arduino licence is cited', str_contains($body, 'Creative Commons Attribution-Share Alike 4.0') && str_contains($bodyEn, 'Creative Commons Attribution-Share Alike 4.0'));
check105('relay photo is cited and pin-order copying is forbidden', str_contains($body, 'kit-relay-5v.jpg') && str_contains($body, 'SRD-05VDC-SL-C_5V_one-channel_relay_module.jpg') && str_contains($body, 'Jangan menyalin urutan kaki dari foto') && str_contains($bodyEn, 'Do not copy pin order from the photo'));
check105('relay wiring is explicit', str_contains($body, 'GPIO 26') && str_contains($body, '5V') && str_contains($body, 'IN atau S'));
check105('pin guessing is forbidden', str_contains($body, 'Jangan menebak pin') && str_contains($bodyEn, 'Do not guess pins'));
check105('no AC mains in the lab', str_contains($body, 'Jangan colok AC 220V') && str_contains($bodyEn, 'Do not connect AC mains') && str_contains($body, 'NC / COM / NO'));
check105('LAN boundary is explicit', str_contains($body, 'IPv4 PC dari <code>ipconfig</code>') && str_contains($body, '<code>127.0.0.1</code>') && str_contains($body, 'guest Wi-Fi'));
check105('PowerShell paste is explained', str_contains($body, 'Cara menempel perintah') && str_contains($bodyEn, 'How to paste'));
check105('expected broker output is explained', str_contains($body, 'terlihat angka <code>1883</code>') && str_contains($bodyEn, 'shows port <code>1883</code>'));
check105('keep broker window open', str_contains($body, 'Biarkan jendela ini terbuka') && str_contains($bodyEn, 'Keep it open'));
check105('Connect is allowed after broker runs', str_contains($body, 'Baru sekarang klik <em>New Connection</em>') && str_contains($bodyEn, 'Only now click <em>New Connection</em>'));
check105('MQTTX host is PC IPv4 not loopback', str_contains($body, 'bukan <code>127.0.0.1</code>') && str_contains($bodyEn, 'not <code>127.0.0.1</code>'));
check105('no router or public broker instruction', str_contains($body, 'Jangan membuka port router') && str_contains($body, 'broker publik') && str_contains($body, 'Private networks'));
check105('public broker is defined in plain language', str_contains($body, 'broker di internet milik pihak lain') && str_contains($bodyEn, 'broker on the internet owned by someone else'));
check105('temporary listener is scoped', str_contains($body, 'listener_allow_anonymous true') && str_contains($body, 'lab LAN singkat') && str_contains($body, 'Ctrl+C'));
check105('FS-33 listener contrast is explained', str_contains($body, 'FS-33 tidak memakai') && str_contains($bodyEn, 'FS-33 did not use a'));
check105('LAN command risk is explained', str_contains($body, 'Siapa saja di Wi-Fi rumah') && str_contains($bodyEn, 'Anyone on the same home Wi-Fi'));
check105('macOS and Linux open Terminal first', str_contains($body, 'buka aplikasi <strong>Terminal</strong> dulu') && str_contains($bodyEn, 'open the <strong>Terminal</strong> app first'));
check105('sketch uses supported MQTT and JSON pattern', str_contains($body, '#include &lt;ArduinoMqttClient.h&gt;') && str_contains($body, 'mqttClient.subscribe(TOPIC_COMMAND)') && str_contains($body, 'mqttClient.onMessage(onMqttMessage)'));
check105('sketch identifies device command and status topics', str_contains($body, 'const char DEVICE_ID[]') && str_contains($body, 'kodingindonesia/fsiot/esp32-meja-01/command') && str_contains($body, 'kodingindonesia/fsiot/esp32-meja-01/status'));
check105('sketch resubscribes after reconnect', str_contains($body, 'Subscribe command siap.') && str_contains($body, 'mqttClient.poll()') && str_contains($body, 'lastMqttAttemptAt'));
check105('relay pin and active-low default', str_contains($body, 'RELAY_PIN = 26') && str_contains($body, 'AKTIF_LOW = true'));
check105('DHT22 is explicitly unused today', str_contains($body, 'DHT22 <strong>tidak</strong> dipakai hari ini') && str_contains($bodyEn, 'DHT22 is <strong>not</strong> used today'));
check105('command JSON example is exact', str_contains($body, '{"device_id":"esp32-meja-01","relay":"on"}'));
check105('no real secret placeholder', str_contains($body, 'GANTI_NAMA_WIFI') && str_contains($body, 'GANTI_SANDI_WIFI'));
check105('official sources are cited', str_contains($body, 'mosquitto.org/man/mosquitto-conf-5.html') && str_contains($body, 'docs.espressif.com') && str_contains($body, 'arduino-libraries/ArduinoMqttClient') && str_contains($body, 'arduinojson.org') && str_contains($body, 'mqttx.app'));
check105('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check105('cover uses the public FS-35 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs35-cover-command.webp'));
check105('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check105('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check105('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check105('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check105('seven FS-35 image figures in both languages', substr_count($body, '/images/fsiot/fs35-') === 7 && substr_count($bodyEn, '/images/fsiot/fs35-') === 7);
check105('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-35)') === 5 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-35)') === 5);
check105('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check105('interactive checklist is wired', str_contains($body, 'id="fsiot-command-checklist"') && str_contains($body, 'id="fsiot-command-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-105'") && str_contains($langId, 'fsiot_command_badge') && str_contains($langEn, 'fsiot_command_badge'));
check105('ten checklist items match in both languages', checklistItems105($body) === 10 && checklistItems105($bodyEn) === 10);
check105('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-command-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-command-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check105('next module is FS-36', str_contains($body, 'FS-36') && str_contains($bodyEn, 'FS-36'));
check105('EYD avoids sungguhan', ! str_contains($body, 'sungguhan') && str_contains($body, 'Pesan yang sama harus muncul'));
check105('EYD uses telemetri not English telemetry in ID', str_contains($body, 'telemetri') && ! str_contains($body, 'telemetry'));
check105('diagram warning copy is a lab note not an error banner', str_contains(file_get_contents($root.'/scripts/gen-fs35-assets.py'), "Catatan lab:") && str_contains(file_get_contents($root.'/scripts/gen-fs35-assets.py'), "'#b45309'") && ! str_contains(file_get_contents($root.'/scripts/gen-fs35-assets.py'), "'#b91c1c'"));
check105('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check105('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check105('deploy hook needles for FS-35', str_contains($controller, "'Subscribe command siap.'") && str_contains($controller, "'listener_allow_anonymous'") && str_contains($controller, "'FS-36'"));
check105('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
preg_match("/'fsiot_command_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_command_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check105('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));

foreach ([
    'fs35-cover-command.jpg',
    'fs35-cover-command.webp',
    'fs35-tools-order.png',
    'fs35-library-manager.png',
    'fs35-wiring-relay.png',
    'fs35-lan-address.png',
    'fs35-command-flow.png',
    'fs35-mqttx-publish.png',
    'fs35-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check105($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$mqttxSize = getimagesize($root.'/public/images/fsiot/fs35-mqttx-publish.png');
check105('MQTTX illustration is cropped to a readable height', $mqttxSize !== false && $mqttxSize[1] <= 800);
$librarySize = getimagesize($root.'/public/images/fsiot/fs35-library-manager.png');
check105('library illustration is cropped to a readable height', $librarySize !== false && $librarySize[1] <= 800);
$kitSize = getimagesize($root.'/public/images/fsiot/kit-relay-5v.jpg');
check105('kit-relay-5v.jpg exists and is readable', $kitSize !== false && $kitSize[0] >= 400);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
