<?php

/** Static quality gate for Article #116 / FS-46. Run: php scripts/audit-article116.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article116Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs46-assets.py');

$seeder = new Database\Seeders\Article116Seeder();
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
$dashMethod = $reflection->getMethod('dashboard');
$dashMethod->setAccessible(true);
$dashboard = $dashMethod->invoke($seeder);
$reqMethod = $reflection->getMethod('requirements');
$reqMethod->setAccessible(true);
$requirements = $reqMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check116(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems116(string $html): int
{
    $chunk = explode('id="fsiot-control-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check116('draft status', str_contains($source, "'status' => 'draft'"));
check116('null publication date', str_contains($source, "'published_at' => null"));
check116('expected slug', str_contains($source, 'fullstack-iot-dashboard-on-off-relay-flask'));
check116('route and controller exist', str_contains($routes, 'seed-article-116-draft') && str_contains($controller, 'seedArticle116Draft'));
check116('priority deploy and seed exist', str_contains($workflow, 'id: curl116_priority') && str_contains($workflow, 'seed-article-116-draft'));
check116('priority upload precedes FS-45 uploads', strpos($workflow, 'id: curl116_priority') < strpos($workflow, 'id: curl115_priority'));
check116('FS-46 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl116_priority.conclusion == 'success'"));
check116('late FS-46 seed is required after FTP', str_contains($workflow, 'Seed article 116 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-dashboard-on-off-relay-flask'));
check116('late FS-46 seed precedes FS-45 seed', strpos($workflow, 'Seed article 116 draft via deploy hook (required, pre-launch B)') < strpos($workflow, 'Seed article 115 draft via deploy hook (required, pre-launch B)'));
check116('FS-46 images are in the priority upload', str_contains($workflow, 'fs46-cover-control.webp') && str_contains($workflow, 'fs46-tools-order.png') && str_contains($workflow, 'fs46-flask-serve.png') && str_contains($workflow, 'fs46-browser-panel.png') && str_contains($workflow, 'fs46-post-flow.png') && str_contains($workflow, 'fs46-troubleshooting.png'));
check116('cover is copied into public storage', str_contains($source, 'articles/covers/fs46-cover-control') && str_contains($source, "Storage::disk('public')->put"));
check116('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check116('ID and EN references', str_contains($body, '#116 (ini)') && str_contains($bodyEn, '#116 (this article)'));
check116('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check116('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu File Explorer') && str_contains($body, 'Buka dulu Notepad') && str_contains($body, 'Buka dulu PowerShell') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open File Explorer first') && str_contains($bodyEn, 'Open Notepad first') && str_contains($bodyEn, 'Open PowerShell first'));
check116('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses =') && str_contains($bodyEn, 'success ='));
check116('File Explorer is opened before Python scripts', strpos($body, 'Buka File Explorer') < strpos($body, '.venv\Scripts\python.exe pintu_stasiun.py') && str_contains($bodyEn, 'Do not type Python commands yet'));
check116('MQTTX is in the five-step order', str_contains($body, 'Buka MQTTX') && str_contains($bodyEn, 'Open MQTTX') && str_contains($body, '127.0.0.1:1883'));
check116('flask version is pinned', str_contains($requirements, 'flask==3.1.3') && str_contains($body, 'flask==3.1.3') && str_contains($bodyEn, 'flask==3.1.3'));
check116('paho pin is kept in requirements', str_contains($requirements, 'paho-mqtt==2.1.0'));
check116('pip is invoked as venv python -m pip', str_contains($body, '.venv\Scripts\python.exe -m pip install -r requirements.txt') && str_contains($bodyEn, '.venv\Scripts\python.exe -m pip install -r requirements.txt'));
check116('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check116('Flask is localhost for PC scripts', str_contains($pintu, 'HOST = "127.0.0.1"') && str_contains($pintu, 'PORT = 5000') && str_contains($body, '127.0.0.1:5000'));
check116('GET reads SQLite not MariaDB', str_contains($pintu, 'stasiun.db') && str_contains($pintu, 'sqlite3.connect') && ! str_contains($pintu, 'mysql') && str_contains($body, 'stasiun.db'));
check116('Flask serves dashboard.html at GET /', str_contains($pintu, 'send_from_directory') && str_contains($pintu, 'dashboard.html') && str_contains($pintu, '@app.get("/")'));
check116('command and status endpoints exist', str_contains($pintu, '@app.post("/command")') && str_contains($pintu, '@app.get("/status")') && str_contains($pintu, 'CREATE TABLE IF NOT EXISTS commands'));
check116('page fetch posts command and reads status', str_contains($dashboard, 'fetch("/command"') && str_contains($dashboard, 'fetch("/status")') && str_contains($dashboard, 'Perintah terkirim.') && str_contains($dashboard, 'tombol-on'));
check116('double-submit lock exists', str_contains($dashboard, 'sedang') && str_contains($dashboard, 'disabled') && str_contains($body, 'sedang') && str_contains($bodyEn, 'sedang'));
check116('switch labels are locked', str_contains($dashboard, 'Sakelar: ON') && str_contains($dashboard, 'Sakelar: OFF') && str_contains($body, 'Sakelar: ON') && str_contains($bodyEn, 'Sakelar: ON'));
check116('history door is kept from FS-45', str_contains($pintu, '@app.get("/history")') && str_contains($pintu, 'MAX_POINTS = 60') && str_contains($dashboard, 'fetch("/history?hours=1")'));
check116('GET still filters by device_id query', str_contains($pintu, 'request.args.get("device_id")') && str_contains($pintu, 'topic_command'));
check116('open line is locked', str_contains($pintu, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($body, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($bodyEn, 'Pintu stasiun terbuka di http://127.0.0.1:5000'));
check116('open-page line is locked', str_contains($pintu, 'Buka http://127.0.0.1:5000') && str_contains($body, 'Buka http://127.0.0.1:5000') && str_contains($bodyEn, 'Buka http://127.0.0.1:5000'));
check116('command print line is locked', str_contains($pintu, 'POST http://127.0.0.1:5000/command') && str_contains($pintu, 'GET  http://127.0.0.1:5000/status'));
check116('status text is locked', str_contains($body, 'Perintah terkirim.') && str_contains($bodyEn, 'Perintah terkirim.') && str_contains($dashboard, 'Perintah terkirim.'));
check116('file protocol is forbidden as the main path', str_contains($body, 'file://') && str_contains($bodyEn, 'file://') && str_contains($body, 'Jangan buka berkas HTML lewat') && str_contains($bodyEn, 'Do not open the HTML file through'));
check116('CORS library is not the pass gate', str_contains($body, 'flask-cors') && str_contains($body, 'Jangan') && ! str_contains($pintu, 'flask_cors') && ! str_contains($pintu, 'CORS'));
check116('MariaDB is not required', str_contains($body, 'tidak wajib') && str_contains($bodyEn, 'not required') && str_contains($body, 'SQLite') && str_contains($bodyEn, 'SQLite'));
check116('no mysql in Flask scripts', ! str_contains($pintu.$dashboard, 'mysql') && ! str_contains($pintu, 'MariaDB'));
check116('Telegram is deferred to FS-47', str_contains($body, 'Telegram') && str_contains($bodyEn, 'Telegram') && str_contains($body, 'FS-47') && str_contains($bodyEn, 'FS-47'));
check116('uji_perintah is not the pass path', str_contains($body, 'uji_perintah.py') && str_contains($body, 'Jangan') && str_contains($bodyEn, 'uji_perintah.py'));
check116('no ESP32 sketch or new GPIO wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check116('no AC mains', str_contains($body, 'Bukan AC 220V') && str_contains($bodyEn, 'Not AC mains') && str_contains($body, 'NC/COM/NO'));
check116('ESP32 may stay on or unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check116('same lab folder as FS-39', str_contains($body, 'Documents\\fsiot-fs39') && str_contains($bodyEn, 'Documents\\fsiot-fs39'));
check116('Notepad is named before script listings', strpos($body, 'Buka dulu Notepad') < strpos($body, 'pintu_stasiun.py</code> dengan kode') && str_contains($bodyEn, 'Open Notepad first'));
check116('File Explorer is named before folder work', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check116('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check116('cover uses the public FS-46 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs46-cover-control.webp'));
check116('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check116('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check116('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check116('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check116('nine FS-46 image figures in both languages', substr_count($body, '/images/fsiot/fs46-') === 9 && substr_count($bodyEn, '/images/fsiot/fs46-') === 9);
check116('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-46)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-46)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-46)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-46)') === 2);
check116('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check116('interactive checklist is wired', str_contains($body, 'id="fsiot-control-checklist"') && str_contains($body, 'id="fsiot-control-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-116'") && str_contains($blade, 'initFsiotControlChecklist') && str_contains($langId, 'fsiot_control_badge') && str_contains($langEn, 'fsiot_control_badge'));
check116('ten checklist items match in both languages', checklistItems116($body) === 10 && checklistItems116($bodyEn) === 10);
check116('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-control-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-control-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check116('next module is FS-47 Telegram', str_contains($body, 'FS-47') && str_contains($bodyEn, 'FS-47') && str_contains($body, 'Telegram') && str_contains($bodyEn, 'Telegram'));
check116('Flask and MQTT docs are cited', str_contains($body, 'flask.palletsprojects.com') && str_contains($body, 'pypi.org/project/Flask/3.1.3') && str_contains($body, 'mqttx.app') && str_contains($bodyEn, 'flask.palletsprojects.com'));
check116('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check116('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check116('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check116('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check116('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check116('deploy hook needles for FS-46', str_contains($controller, "'#116 (ini)'") && str_contains($controller, "'Perintah terkirim.'") && str_contains($controller, "'/command'") && str_contains($controller, "'Sakelar: ON'") && str_contains($controller, "'FS-47'"));
check116('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check116('script file names are locked', str_contains($body, 'dashboard.html') && str_contains($body, 'pintu_stasiun.py') && str_contains($bodyEn, 'dashboard.html'));
check116('mqtt client id is unique to FS-46', str_contains($pintu, 'fsiot-fs46-pintu'));
check116('seo title stays within clamp', mb_strlen('Tombol ON/OFF Dashboard — FS-46') <= 70 && mb_strlen('Show ON/OFF Buttons — FS-46') <= 70);
preg_match("/'fsiot_control_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_control_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check116('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_control_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check116('pass checklist copy mentions the command', str_contains($passId[1] ?? '', 'Perintah terkirim'));

foreach ([
    'fs46-cover-control.jpg',
    'fs46-cover-control.webp',
    'fs46-tools-order.png',
    'fs46-why-buttons.png',
    'fs46-post-flow.png',
    'fs46-double-submit.png',
    'fs46-status.png',
    'fs46-flask-serve.png',
    'fs46-browser-panel.png',
    'fs46-mqttx.png',
    'fs46-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check116($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$panelSize = getimagesize($root.'/public/images/fsiot/fs46-browser-panel.png');
check116('browser panel illustration is cropped to a readable height', $panelSize !== false && $panelSize[1] <= 800);
$mqttxSize = getimagesize($root.'/public/images/fsiot/fs46-mqttx.png');
check116('MQTTX illustration is cropped to a readable height', $mqttxSize !== false && $mqttxSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
