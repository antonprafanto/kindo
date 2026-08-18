<?php

/** Static quality gate for Article #117 / FS-47. Run: php scripts/audit-article117.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article117Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs47-assets.py');

$seeder = new Database\Seeders\Article117Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);
$waspadaMethod = $reflection->getMethod('waspada');
$waspadaMethod->setAccessible(true);
$waspada = $waspadaMethod->invoke($seeder);
$rahasiaMethod = $reflection->getMethod('rahasia');
$rahasiaMethod->setAccessible(true);
$rahasia = $rahasiaMethod->invoke($seeder);
$reqMethod = $reflection->getMethod('requirements');
$reqMethod->setAccessible(true);
$requirements = $reqMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check117(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems117(string $html): int
{
    $chunk = explode('id="fsiot-telegram-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check117('draft status', str_contains($source, "'status' => 'draft'"));
check117('null publication date', str_contains($source, "'published_at' => null"));
check117('expected slug', str_contains($source, 'fullstack-iot-telegram-alert-ambang-stasiun'));
check117('route and controller exist', str_contains($routes, 'seed-article-117-draft') && str_contains($controller, 'seedArticle117Draft'));
check117('priority deploy and seed exist', str_contains($workflow, 'id: curl117_priority') && str_contains($workflow, 'seed-article-117-draft'));
check117('priority upload precedes FS-46 uploads', strpos($workflow, 'id: curl117_priority') < strpos($workflow, 'id: curl116_priority'));
check117('FS-47 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl117_priority.conclusion == 'success'"));
check117('late FS-47 seed is required after FTP', str_contains($workflow, 'Seed article 117 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-telegram-alert-ambang-stasiun'));
check117('late FS-47 seed precedes FS-46 seed', strpos($workflow, 'Seed article 117 draft via deploy hook (required, pre-launch B)') < strpos($workflow, 'Seed article 116 draft via deploy hook (required, pre-launch B)'));
check117('FS-47 images are in the priority upload', str_contains($workflow, 'fs47-cover-alert.webp') && str_contains($workflow, 'fs47-tools-order.png') && str_contains($workflow, 'fs47-threshold-flow.png') && str_contains($workflow, 'fs47-botfather.png') && str_contains($workflow, 'fs47-phone-chat.png') && str_contains($workflow, 'fs47-troubleshooting.png'));
check117('cover is copied into public storage', str_contains($source, 'articles/covers/fs47-cover-alert') && str_contains($source, "Storage::disk('public')->put"));
check117('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check117('ID and EN references', str_contains($body, '#117 (ini)') && str_contains($bodyEn, '#117 (this article)'));
check117('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check117('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu File Explorer') && str_contains($body, 'Buka dulu Notepad') && str_contains($body, 'Buka dulu PowerShell') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open File Explorer first') && str_contains($bodyEn, 'Open Notepad first') && str_contains($bodyEn, 'Open PowerShell first'));
check117('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses =') && str_contains($bodyEn, 'success ='));
check117('File Explorer is opened before Python scripts', strpos($body, 'Buka File Explorer') < strpos($body, '.venv\Scripts\python.exe waspada_telegram.py') && str_contains($bodyEn, 'Do not type Python commands yet'));
check117('MQTTX is in the five-step order', str_contains($body, 'Buka MQTTX') && str_contains($bodyEn, 'Open MQTTX') && str_contains($body, '127.0.0.1:1883'));
check117('paho pin is kept in requirements', str_contains($requirements, 'paho-mqtt==2.1.0') && str_contains($body, 'paho-mqtt==2.1.0') && str_contains($bodyEn, 'paho-mqtt==2.1.0'));
check117('pip is invoked as venv python -m pip', str_contains($body, '.venv\Scripts\python.exe -m pip install -r requirements.txt') && str_contains($bodyEn, '.venv\Scripts\python.exe -m pip install -r requirements.txt'));
check117('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check117('secret file uses placeholders', str_contains($rahasia, 'TOKEN=GANTI_TOKEN') && str_contains($rahasia, 'CHAT_ID=GANTI_CHAT_ID') && str_contains($body, 'telegram_rahasia.txt'));
check117('script uses stdlib urllib not extra bot library', str_contains($waspada, 'urllib.request') && str_contains($waspada, 'sendMessage') && str_contains($waspada, 'getUpdates') && ! str_contains($waspada, 'python-telegram-bot') && ! str_contains($waspada, 'import requests'));
check117('Bot API sendMessage and getUpdates are the pass path', str_contains($waspada, 'sendMessage') && str_contains($body, 'sendMessage') && str_contains($bodyEn, 'getUpdates'));
check117('threshold and cooldown are locked', str_contains($waspada, 'AMBANG = 30.0') && str_contains($waspada, 'COOLDOWN_DETIK = 60') && str_contains($body, 'Cooldown: alert ditahan.') && str_contains($bodyEn, 'Cooldown: alert ditahan.'));
check117('alert print line is locked', str_contains($waspada, 'Alert terkirim ke Telegram.') && str_contains($body, 'Alert terkirim ke Telegram.') && str_contains($bodyEn, 'Alert terkirim ke Telegram.'));
check117('open watcher line is locked', str_contains($waspada, 'Waspada Telegram terbuka. Menunggu telemetri.') && str_contains($body, 'Waspada Telegram terbuka. Menunggu telemetri.') && str_contains($bodyEn, 'Waspada Telegram terbuka. Menunggu telemetri.'));
check117('script does not print the token', ! str_contains($waspada, 'print(token') && ! str_contains($waspada, 'print("TOKEN') && str_contains($body, 'tidak mencetak token'));
check117('MQTT telemetry topic is locked', str_contains($waspada, 'kodingindonesia/fsiot/esp32-meja-01/telemetry') && str_contains($body, 'temperature_c":31.2'));
check117('broker localhost is locked', str_contains($waspada, 'BROKER = "127.0.0.1"') && str_contains($waspada, 'PORT = 1883') && str_contains($waspada, 'Broker belum terbuka di 127.0.0.1:1883'));
check117('paho v2 callback is required', str_contains($waspada, 'CallbackAPIVersion.VERSION2') && str_contains($body, 'CallbackAPIVersion.VERSION2'));
check117('file protocol is forbidden as the main path', str_contains($body, 'file://') && str_contains($bodyEn, 'file://'));
check117('MariaDB is not required', str_contains($body, 'tidak wajib') && str_contains($bodyEn, 'not required') && str_contains($body, 'SQLite') && str_contains($bodyEn, 'SQLite'));
check117('no mysql in lab scripts', ! str_contains($waspada.$rahasia, 'mysql') && ! str_contains($waspada, 'MariaDB'));
check117('Flask dashboard is not the pass path', str_contains($body, 'tidak wajib dibuka') && str_contains($bodyEn, 'does not have to stay open') && ! str_contains($waspada, 'flask'));
check117('Node-RED is not the pass path', str_contains($body, 'Node-RED') && str_contains($body, 'bukan') && str_contains($bodyEn, 'Node-RED'));
check117('webhook and email are named not required', str_contains($body, 'webhook') && str_contains($body, 'email') && str_contains($bodyEn, 'webhook') && str_contains($bodyEn, 'email'));
check117('ngrok is forbidden', str_contains($body, 'ngrok') && str_contains($bodyEn, 'ngrok'));
check117('no ESP32 sketch or new GPIO wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check117('no AC mains', str_contains($body, 'Bukan AC 220V') && str_contains($bodyEn, 'Not AC mains') && str_contains($body, 'NC/COM/NO'));
check117('ESP32 may stay on or unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check117('same lab folder as FS-39', str_contains($body, 'Documents\\fsiot-fs39') && str_contains($bodyEn, 'Documents\\fsiot-fs39'));
check117('Notepad is named before script listings', strpos($body, 'Buka dulu Notepad') < strpos($body, 'waspada_telegram.py</code> dengan kode') && str_contains($bodyEn, 'Open Notepad first'));
check117('File Explorer is named before folder work', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check117('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check117('cover uses the public FS-47 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs47-cover-alert.webp'));
check117('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check117('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check117('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check117('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check117('nine FS-47 image figures in both languages', substr_count($body, '/images/fsiot/fs47-') === 9 && substr_count($bodyEn, '/images/fsiot/fs47-') === 9);
check117('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-47)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-47)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-47)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-47)') === 2);
check117('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check117('interactive checklist is wired', str_contains($body, 'id="fsiot-telegram-checklist"') && str_contains($body, 'id="fsiot-telegram-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-117'") && str_contains($blade, 'initFsiotTelegramChecklist') && str_contains($langId, 'fsiot_telegram_badge') && str_contains($langEn, 'fsiot_telegram_badge'));
check117('ten checklist items match in both languages', checklistItems117($body) === 10 && checklistItems117($bodyEn) === 10);
check117('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-telegram-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-telegram-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check117('next module is FS-48 UX', str_contains($body, 'FS-48') && str_contains($bodyEn, 'FS-48') && str_contains($body, 'data basi') && str_contains($bodyEn, 'stale'));
check117('Telegram and MQTT docs are cited', str_contains($body, 'core.telegram.org/bots/api') && str_contains($body, 'core.telegram.org/bots/api#sendmessage') && str_contains($body, 'mqttx.app') && str_contains($bodyEn, 'core.telegram.org/bots/api'));
check117('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check117('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check117('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check117('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check117('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check117('deploy hook needles for FS-47', str_contains($controller, "'#117 (ini)'") && str_contains($controller, "'sendMessage'") && str_contains($controller, "'waspada_telegram.py'") && str_contains($controller, "'telegram_rahasia.txt'") && str_contains($controller, "'FS-48'"));
check117('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check117('script file names are locked', str_contains($body, 'waspada_telegram.py') && str_contains($body, 'telegram_rahasia.txt') && str_contains($bodyEn, 'waspada_telegram.py'));
check117('mqtt client id is unique to FS-47', str_contains($waspada, 'fsiot-fs47-waspada'));
check117('seo title stays within clamp', mb_strlen('Alert Telegram saat suhu melewati ambang — FS-47') <= 70 && mb_strlen('Telegram alert when temperature crosses the limit — FS-47') <= 70);
check117('seo description stays within clamp', mb_strlen('Lab pemula: BotFather, token di berkas rahasia, sendMessage saat suhu di atas ambang, cooldown. Bukan MySQL, bukan screenshot token, bukan AC 220V.') <= 160 && mb_strlen('A first lab: BotFather, token in a secret file, sendMessage when temperature crosses the limit, cooldown. Not MySQL, not a token screenshot, not AC mains.') <= 160);
preg_match("/'fsiot_telegram_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_telegram_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check117('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_telegram_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check117('pass checklist copy mentions the alert', str_contains($passId[1] ?? '', 'Alert terkirim ke Telegram'));

foreach ([
    'fs47-cover-alert.jpg',
    'fs47-cover-alert.webp',
    'fs47-tools-order.png',
    'fs47-why-alert.png',
    'fs47-threshold-flow.png',
    'fs47-secret-file.png',
    'fs47-getupdates.png',
    'fs47-cooldown.png',
    'fs47-botfather.png',
    'fs47-phone-chat.png',
    'fs47-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check117($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$botfatherSize = getimagesize($root.'/public/images/fsiot/fs47-botfather.png');
check117('BotFather illustration is cropped to a readable height', $botfatherSize !== false && $botfatherSize[1] <= 800);
$phoneSize = getimagesize($root.'/public/images/fsiot/fs47-phone-chat.png');
check117('phone chat illustration is cropped to a readable height', $phoneSize !== false && $phoneSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
