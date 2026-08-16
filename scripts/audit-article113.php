<?php

/** Static quality gate for Article #113 / FS-43. Run: php scripts/audit-article113.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article113Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs43-assets.py');

$seeder = new Database\Seeders\Article113Seeder();
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
$isiMethod = $reflection->getMethod('isi');
$isiMethod->setAccessible(true);
$isi = $isiMethod->invoke($seeder);
$ujiMethod = $reflection->getMethod('uji');
$ujiMethod->setAccessible(true);
$uji = $ujiMethod->invoke($seeder);
$reqMethod = $reflection->getMethod('requirements');
$reqMethod->setAccessible(true);
$requirements = $reqMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check113(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems113(string $html): int
{
    $chunk = explode('id="fsiot-device-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check113('draft status', str_contains($source, "'status' => 'draft'"));
check113('null publication date', str_contains($source, "'published_at' => null"));
check113('expected slug', str_contains($source, 'fullstack-iot-device-id-dua-stasiun'));
check113('route and controller exist', str_contains($routes, 'seed-article-113-draft') && str_contains($controller, 'seedArticle113Draft'));
check113('priority deploy and seed exist', str_contains($workflow, 'id: curl113_priority') && str_contains($workflow, 'seed-article-113-draft'));
check113('priority upload precedes FS-42 uploads', strpos($workflow, 'id: curl113_priority') < strpos($workflow, 'id: curl112_priority'));
check113('FS-43 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl113_priority.conclusion == 'success'"));
check113('late FS-43 seed is required after FTP', str_contains($workflow, 'Seed article 113 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-device-id-dua-stasiun'));
check113('late FS-43 seed precedes FS-42 seed', strpos($workflow, 'Seed article 113 draft via deploy hook (required, pre-launch B)') < strpos($workflow, 'Seed article 112 draft via deploy hook (required, pre-launch B)'));
check113('FS-43 images are in the priority upload', str_contains($workflow, 'fs43-cover-device.webp') && str_contains($workflow, 'fs43-tools-order.png') && str_contains($workflow, 'fs43-filter.png') && str_contains($workflow, 'fs43-browser-json.png') && str_contains($workflow, 'fs43-two-names.png') && str_contains($workflow, 'fs43-troubleshooting.png'));
check113('cover is copied into public storage', str_contains($source, 'articles/covers/fs43-cover-device') && str_contains($source, "Storage::disk('public')->put"));
check113('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check113('ID and EN references', str_contains($body, '#113 (ini)') && str_contains($bodyEn, '#113 (this article)'));
check113('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check113('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu MQTTX') && str_contains($body, 'Buka dulu Notepad') && str_contains($body, 'Buka dulu PowerShell') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open MQTTX first') && str_contains($bodyEn, 'Open Notepad first') && str_contains($bodyEn, 'Open PowerShell first'));
check113('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses =') && str_contains($bodyEn, 'success ='));
check113('MQTTX is opened before Python scripts', strpos($body, 'Buka MQTTX') < strpos($body, 'isi_dua_stasiun.py</code>, folder') && str_contains($bodyEn, 'Do not type Python commands yet'));
check113('flask version is pinned', str_contains($requirements, 'flask==3.1.3') && str_contains($body, 'flask==3.1.3') && str_contains($bodyEn, 'flask==3.1.3'));
check113('paho pin is kept in requirements', str_contains($requirements, 'paho-mqtt==2.1.0'));
check113('pip is invoked as venv python -m pip', str_contains($body, '.venv\Scripts\python.exe -m pip install -r requirements.txt') && str_contains($bodyEn, '.venv\Scripts\python.exe -m pip install -r requirements.txt'));
check113('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check113('Flask is localhost for PC scripts', str_contains($pintu, 'HOST = "127.0.0.1"') && str_contains($pintu, 'PORT = 5000') && str_contains($body, '127.0.0.1:5000'));
check113('GET reads SQLite not MariaDB', str_contains($pintu, 'stasiun.db') && str_contains($pintu, 'sqlite3.connect') && ! str_contains($pintu, 'mysql') && str_contains($body, 'stasiun.db'));
check113('GET filters by device_id query', str_contains($pintu, 'request.args.get("device_id")') && str_contains($body, '?device_id=esp32-meja-02') && str_contains($bodyEn, '?device_id=esp32-meja-02'));
check113('POST publishes command topic from device_id', str_contains($pintu, 'topic_command') && str_contains($pintu, 'kodingindonesia/fsiot/{device_id}/command') && str_contains($uji, 'esp32-meja-02'));
check113('isi script locks meja-02 rows', str_contains($isi, '5 baris meja-02 siap.') && str_contains($isi, 'esp32-meja-02') && str_contains($body, '5 baris meja-02 siap.') && str_contains($bodyEn, '5 baris meja-02 siap.'));
check113('GET success JSON is locked', str_contains($body, '"jumlah": 5') && str_contains($bodyEn, '"jumlah": 5') && str_contains($body, '"device_id": "esp32-meja-02"'));
check113('open line is locked', str_contains($pintu, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($body, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($bodyEn, 'Pintu stasiun terbuka di http://127.0.0.1:5000'));
check113('POST helper prints locked line', str_contains($uji, 'Perintah terkirim.') && str_contains($body, 'Perintah terkirim.') && str_contains($bodyEn, 'Perintah terkirim.'));
check113('bonus MQTT publish is optional', str_contains($body, 'Tidak wajib') && str_contains($bodyEn, 'Not required') && str_contains($body, 'kirim_dua_stasiun.py') && str_contains($bodyEn, 'kirim_dua_stasiun.py'));
check113('MariaDB is not required', str_contains($body, 'tidak wajib') && str_contains($bodyEn, 'not required') && str_contains($body, 'SQLite') && str_contains($bodyEn, 'SQLite'));
check113('no mysql in Flask scripts', ! str_contains($pintu.$isi.$uji, 'mysql') && ! str_contains($pintu.$isi.$uji, 'MariaDB'));
check113('body defers HTML dashboard', str_contains($body, 'belum dashboard') && str_contains($bodyEn, 'no HTML dashboard'));
check113('no ESP32 sketch or new wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check113('no AC mains', str_contains($body, 'Bukan AC 220V') && str_contains($bodyEn, 'Not AC mains'));
check113('ESP32 may stay on or unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check113('same lab folder as FS-39', str_contains($body, 'Documents\\fsiot-fs39') && str_contains($bodyEn, 'Documents\\fsiot-fs39'));
check113('Notepad is named before script listings', strpos($body, 'Buka dulu Notepad') < strpos($body, 'isi_dua_stasiun.py</code>, folder') && str_contains($bodyEn, 'Open Notepad first'));
check113('File Explorer is named before folder work', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check113('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check113('cover uses the public FS-43 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs43-cover-device.webp'));
check113('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check113('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check113('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check113('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check113('nine FS-43 image figures in both languages', substr_count($body, '/images/fsiot/fs43-') === 9 && substr_count($bodyEn, '/images/fsiot/fs43-') === 9);
check113('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-43)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-43)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-43)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-43)') === 2);
check113('Wikimedia board photo is cited not embedded', str_contains($body, 'commons.wikimedia.org') && str_contains($body, 'Ubahnverleih') && str_contains($body, 'CC0') && ! str_contains($body, 'fs43-board') && str_contains($bodyEn, 'commons.wikimedia.org'));
check113('Username is distinguished from device_id', str_contains($body, 'Username MQTTX') && str_contains($body, 'bukan Username') && str_contains($bodyEn, 'MQTTX Username') && str_contains($bodyEn, 'not the MQTTX Username'));
check113('Mosquitto password is not the pass gate', str_contains($body, 'Jangan diubah hari ini') && str_contains($bodyEn, 'Do not change it today') && str_contains($body, 'allow_anonymous'));
check113('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check113('interactive checklist is wired', str_contains($body, 'id="fsiot-device-checklist"') && str_contains($body, 'id="fsiot-device-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-113'") && str_contains($blade, 'initFsiotDeviceChecklist') && str_contains($langId, 'fsiot_device_badge') && str_contains($langEn, 'fsiot_device_badge'));
check113('ten checklist items match in both languages', checklistItems113($body) === 10 && checklistItems113($bodyEn) === 10);
check113('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-device-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-device-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check113('next module is FS-44 dashboard', str_contains($body, 'FS-44') && str_contains($bodyEn, 'FS-44') && str_contains($body, 'HTML') && str_contains($bodyEn, 'HTML'));
check113('Flask docs are cited', str_contains($body, 'flask.palletsprojects.com') && str_contains($body, 'pypi.org/project/Flask/3.1.3') && str_contains($bodyEn, 'flask.palletsprojects.com'));
check113('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check113('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check113('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check113('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check113('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check113('deploy hook needles for FS-43', str_contains($controller, "'#113 (ini)'") && str_contains($controller, "'esp32-meja-02'") && str_contains($controller, "'stasiun.db'") && str_contains($controller, "'5 baris meja-02 siap.'") && str_contains($controller, "'FS-44'"));
check113('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check113('script file names are locked', str_contains($body, 'isi_dua_stasiun.py') && str_contains($body, 'pintu_stasiun.py') && str_contains($body, 'uji_perintah.py') && str_contains($bodyEn, 'isi_dua_stasiun.py'));
check113('seo title stays within clamp', mb_strlen('Saring Dua Stasiun lewat device_id — FS-43') <= 70 && mb_strlen('Filter Two Stations by device_id — FS-43') <= 70);
preg_match("/'fsiot_device_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_device_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check113('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_device_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check113('pass checklist copy mentions filtered rows', str_contains($passId[1] ?? '', '5') && (str_contains($passId[1] ?? '', 'saring') || str_contains($passId[1] ?? '', 'stasiun')));

foreach ([
    'fs43-cover-device.jpg',
    'fs43-cover-device.webp',
    'fs43-tools-order.png',
    'fs43-why-id.png',
    'fs43-topic.png',
    'fs43-mqttx.png',
    'fs43-two-names.png',
    'fs43-filter.png',
    'fs43-browser-json.png',
    'fs43-command-topic.png',
    'fs43-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check113($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$mqttxSize = getimagesize($root.'/public/images/fsiot/fs43-mqttx.png');
check113('MQTTX illustration is cropped to a readable height', $mqttxSize !== false && $mqttxSize[1] <= 800);
$jsonSize = getimagesize($root.'/public/images/fsiot/fs43-browser-json.png');
check113('browser JSON illustration is cropped to a readable height', $jsonSize !== false && $jsonSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
