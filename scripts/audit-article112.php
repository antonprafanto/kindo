<?php

/** Static quality gate for Article #112 / FS-42. Run: php scripts/audit-article112.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article112Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs42-assets.py');

$seeder = new Database\Seeders\Article112Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);
$pintuMethod = $reflection->getMethod('pintu');
$pintuMethod->setAccessible(true);
$pintu = $pintuMethod->invoke($seeder);
$ujiMethod = $reflection->getMethod('uji');
$ujiMethod->setAccessible(true);
$uji = $ujiMethod->invoke($seeder);
$reqMethod = $reflection->getMethod('requirements');
$reqMethod->setAccessible(true);
$requirements = $reqMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check112(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems112(string $html): int
{
    $chunk = explode('id="fsiot-flask-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check112('draft status', str_contains($source, "'status' => 'draft'"));
check112('null publication date', str_contains($source, "'published_at' => null"));
check112('expected slug', str_contains($source, 'fullstack-iot-flask-rest-sqlite-stasiun'));
check112('route and controller exist', str_contains($routes, 'seed-article-112-draft') && str_contains($controller, 'seedArticle112Draft'));
check112('priority deploy and seed exist', str_contains($workflow, 'id: curl112_priority') && str_contains($workflow, 'seed-article-112-draft'));
check112('priority upload precedes FS-41 uploads', strpos($workflow, 'id: curl112_priority') < strpos($workflow, 'id: curl111_priority'));
check112('FS-42 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl112_priority.conclusion == 'success'"));
check112('late FS-42 seed is required after FTP', str_contains($workflow, 'Seed article 112 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-flask-rest-sqlite-stasiun'));
check112('FS-42 images are in the priority upload', str_contains($workflow, 'fs42-cover-flask.webp') && str_contains($workflow, 'fs42-tools-order.png') && str_contains($workflow, 'fs42-routes.png') && str_contains($workflow, 'fs42-browser-json.png') && str_contains($workflow, 'fs42-troubleshooting.png'));
check112('cover is copied into public storage', str_contains($source, 'articles/covers/fs42-cover-flask') && str_contains($source, "Storage::disk('public')->put"));
check112('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check112('ID and EN references', str_contains($body, '#112 (ini)') && str_contains($bodyEn, '#112 (this article)'));
check112('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check112('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu MQTTX') && str_contains($body, 'Buka dulu Notepad') && str_contains($body, 'Buka dulu PowerShell') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open MQTTX first') && str_contains($bodyEn, 'Open Notepad first') && str_contains($bodyEn, 'Open PowerShell first'));
check112('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses =') && str_contains($bodyEn, 'success ='));
check112('MQTTX is opened before pip', strpos($body, 'Buka MQTTX') < strpos($body, 'pip install -r requirements.txt') && str_contains($bodyEn, 'Do not type pip yet'));
check112('flask version is pinned', str_contains($requirements, 'flask==3.1.3') && str_contains($body, 'flask==3.1.3') && str_contains($bodyEn, 'flask==3.1.3'));
check112('paho pin is kept in requirements', str_contains($requirements, 'paho-mqtt==2.1.0'));
check112('pip is invoked as venv python -m pip', str_contains($body, '.venv\Scripts\python.exe -m pip install -r requirements.txt') && str_contains($bodyEn, '.venv\Scripts\python.exe -m pip install -r requirements.txt'));
check112('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check112('Flask is localhost for PC scripts', str_contains($pintu, 'HOST = "127.0.0.1"') && str_contains($pintu, 'PORT = 5000') && str_contains($body, '127.0.0.1:5000'));
check112('GET reads SQLite not MariaDB', str_contains($pintu, 'stasiun.db') && str_contains($pintu, 'sqlite3.connect') && ! str_contains($pintu, 'mysql') && str_contains($body, 'stasiun.db'));
check112('POST publishes MQTT command', str_contains($pintu, 'TOPIC_COMMAND') && str_contains($pintu, 'kodingindonesia/fsiot/esp32-meja-01/command') && str_contains($pintu, 'client.publish'));
check112('GET success JSON is locked', str_contains($pintu, '"jumlah"') && str_contains($body, '"jumlah": 10') && str_contains($bodyEn, '"jumlah": 10'));
check112('open line is locked', str_contains($pintu, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($body, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($bodyEn, 'Pintu stasiun terbuka di http://127.0.0.1:5000'));
check112('POST helper prints locked line', str_contains($uji, 'Perintah terkirim.') && str_contains($body, 'Perintah terkirim.') && str_contains($bodyEn, 'Perintah terkirim.'));
check112('bonus off command is optional', str_contains($body, 'Tidak wajib') && str_contains($bodyEn, 'Not required') && str_contains($body, '"off"') && str_contains($bodyEn, '"off"'));
check112('MariaDB is not required', str_contains($body, 'tidak wajib') && str_contains($bodyEn, 'not required') && str_contains($body, 'SQLite tetap') && str_contains($bodyEn, 'SQLite stays'));
check112('no mysql in Flask scripts', ! str_contains($pintu.$uji, 'mysql') && ! str_contains($pintu.$uji, 'MariaDB'));
check112('body defers HTML dashboard', str_contains($body, 'belum dashboard') && str_contains($bodyEn, 'no HTML dashboard'));
check112('no ESP32 sketch or new wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check112('no AC mains', str_contains($body, 'Bukan AC 220V') && str_contains($bodyEn, 'Not AC mains'));
check112('ESP32 may stay on or unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check112('same lab folder as FS-39', str_contains($body, 'Documents\\fsiot-fs39') && str_contains($bodyEn, 'Documents\\fsiot-fs39'));
check112('Notepad is named before script listings', strpos($body, 'Buka dulu Notepad') < strpos($body, 'pintu_stasiun.py</code>, folder') && str_contains($bodyEn, 'Open Notepad first'));
check112('File Explorer is named before folder work', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check112('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check112('cover uses the public FS-42 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs42-cover-flask.webp'));
check112('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check112('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check112('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check112('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check112('nine FS-42 image figures in both languages', substr_count($body, '/images/fsiot/fs42-') === 9 && substr_count($bodyEn, '/images/fsiot/fs42-') === 9);
check112('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-42)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-42)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-42)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-42)') === 2);
check112('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check112('interactive checklist is wired', str_contains($body, 'id="fsiot-flask-checklist"') && str_contains($body, 'id="fsiot-flask-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-112'") && str_contains($blade, 'initFsiotFlaskChecklist') && str_contains($langId, 'fsiot_flask_badge') && str_contains($langEn, 'fsiot_flask_badge'));
check112('ten checklist items match in both languages', checklistItems112($body) === 10 && checklistItems112($bodyEn) === 10);
check112('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-flask-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-flask-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check112('next module is FS-43 device_id', str_contains($body, 'FS-43') && str_contains($bodyEn, 'FS-43') && str_contains($body, 'device_id') && str_contains($bodyEn, 'device_id'));
check112('Flask docs are cited', str_contains($body, 'flask.palletsprojects.com') && str_contains($body, 'pypi.org/project/Flask/3.1.3') && str_contains($bodyEn, 'flask.palletsprojects.com'));
check112('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check112('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check112('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check112('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check112('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check112('deploy hook needles for FS-42', str_contains($controller, "'#112 (ini)'") && str_contains($controller, "'flask==3.1.3'") && str_contains($controller, "'stasiun.db'") && str_contains($controller, "'Pintu stasiun terbuka'") && str_contains($controller, "'FS-43'"));
check112('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check112('script file names are locked', str_contains($body, 'pintu_stasiun.py') && str_contains($body, 'uji_perintah.py') && str_contains($bodyEn, 'pintu_stasiun.py'));
check112('seo title stays within clamp', mb_strlen('Baca Histori SQLite lewat REST Flask — FS-42') <= 70 && mb_strlen('Read SQLite History through REST Flask — FS-42') <= 70);
preg_match("/'fsiot_flask_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_flask_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check112('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_flask_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check112('pass checklist copy mentions Flask JSON', str_contains($passId[1] ?? '', 'Flask') && str_contains($passId[1] ?? '', '10'));

foreach ([
    'fs42-cover-flask.jpg',
    'fs42-cover-flask.webp',
    'fs42-tools-order.png',
    'fs42-why-api.png',
    'fs42-download.png',
    'fs42-mqttx.png',
    'fs42-pip-venv.png',
    'fs42-routes.png',
    'fs42-browser-json.png',
    'fs42-post-mqtt.png',
    'fs42-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check112($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$mqttxSize = getimagesize($root.'/public/images/fsiot/fs42-mqttx.png');
check112('MQTTX illustration is cropped to a readable height', $mqttxSize !== false && $mqttxSize[1] <= 800);
$jsonSize = getimagesize($root.'/public/images/fsiot/fs42-browser-json.png');
check112('browser JSON illustration is cropped to a readable height', $jsonSize !== false && $jsonSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
