<?php

/** Static quality gate for Article #110 / FS-40. Run: php scripts/audit-article110.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article110Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs40-assets.py');

$seeder = new Database\Seeders\Article110Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);
$terimaMethod = $reflection->getMethod('terima');
$terimaMethod->setAccessible(true);
$terima = $terimaMethod->invoke($seeder);
$kirimMethod = $reflection->getMethod('kirim');
$kirimMethod->setAccessible(true);
$kirim = $kirimMethod->invoke($seeder);
$lihatMethod = $reflection->getMethod('lihat');
$lihatMethod->setAccessible(true);
$lihat = $lihatMethod->invoke($seeder);
$aturanMethod = $reflection->getMethod('aturan');
$aturanMethod->setAccessible(true);
$aturan = $aturanMethod->invoke($seeder);
$reqMethod = $reflection->getMethod('requirements');
$reqMethod->setAccessible(true);
$requirements = $reqMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check110(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems110(string $html): int
{
    $chunk = explode('id="fsiot-sqlite-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check110('draft status', str_contains($source, "'status' => 'draft'"));
check110('null publication date', str_contains($source, "'published_at' => null"));
check110('expected slug', str_contains($source, 'fullstack-iot-python-mqtt-sqlite-stasiun'));
check110('route and controller exist', str_contains($routes, 'seed-article-110-draft') && str_contains($controller, 'seedArticle110Draft'));
check110('priority deploy and seed exist', str_contains($workflow, 'id: curl110_priority') && str_contains($workflow, 'seed-article-110-draft'));
check110('priority upload precedes FS-39 uploads', strpos($workflow, 'id: curl110_priority') < strpos($workflow, 'id: curl109_priority'));
check110('FS-40 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl110_priority.conclusion == 'success'"));
check110('late FS-40 seed is required after FTP', str_contains($workflow, 'Seed article 110 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-python-mqtt-sqlite-stasiun'));
check110('FS-40 images are in the priority upload', str_contains($workflow, 'fs40-cover-sqlite.webp') && str_contains($workflow, 'fs40-tools-order.png') && str_contains($workflow, 'fs40-callback.png') && str_contains($workflow, 'fs40-sqlite.png') && str_contains($workflow, 'fs40-troubleshooting.png'));
check110('cover is copied into public storage', str_contains($source, 'articles/covers/fs40-cover-sqlite') && str_contains($source, "Storage::disk('public')->put"));
check110('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check110('ID and EN references', str_contains($body, '#110 (ini)') && str_contains($bodyEn, '#110 (this article)'));
check110('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check110('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu MQTTX') && str_contains($body, 'Buka dulu Notepad') && str_contains($body, 'Buka dulu PowerShell') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open MQTTX first') && str_contains($bodyEn, 'Open Notepad first') && str_contains($bodyEn, 'Open PowerShell first'));
check110('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses =') && str_contains($bodyEn, 'success ='));
check110('MQTTX is opened before pip', strpos($body, 'Buka MQTTX') < strpos($body, 'pip install -r requirements.txt') && str_contains($bodyEn, 'Do not type pip yet'));
check110('paho version is pinned', $requirements === 'paho-mqtt==2.1.0' && str_contains($body, 'paho-mqtt==2.1.0') && str_contains($bodyEn, 'paho-mqtt==2.1.0'));
check110('pip is invoked as venv python -m pip', str_contains($body, '.venv\Scripts\python.exe -m pip install -r requirements.txt') && str_contains($bodyEn, '.venv\Scripts\python.exe -m pip install -r requirements.txt'));
check110('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check110('broker is localhost for PC scripts', str_contains($terima, 'BROKER = "127.0.0.1"') && str_contains($kirim, 'BROKER = "127.0.0.1"') && str_contains($body, '127.0.0.1'));
check110('telemetry topic matches FS-34', str_contains($terima, 'kodingindonesia/fsiot/esp32-meja-01/telemetry') && str_contains($body, 'kodingindonesia/fsiot/esp32-meja-01/telemetry'));
check110('paho v2 callback API is used', str_contains($terima, 'CallbackAPIVersion.VERSION2') && str_contains($terima, 'loop_forever'));
check110('subscriber stops after ten valid messages', str_contains($terima, 'TARGET = 10') && str_contains($terima, '10 baris tersimpan'));
check110('CSV then SQLite are written', str_contains($terima, 'stasiun.csv') && str_contains($terima, 'stasiun.db') && str_contains($terima, 'CREATE TABLE IF NOT EXISTS telemetry'));
check110('lihat_db reads SQLite', str_contains($lihat, 'stasiun.db') && str_contains($lihat, 'Jumlah baris:'));
check110('sample publisher sends ten JSON messages', substr_count($kirim, 'range(10)') === 1 && str_contains($kirim, 'temperature_c'));
check110('bonus rules publish FS-35 command JSON', str_contains($aturan, 'TOPIC_COMMAND') && str_contains($aturan, '"relay": relay') && str_contains($aturan, 'AMBANG = 30.0'));
check110('bonus is optional', str_contains($body, 'Tidak wajib') && str_contains($bodyEn, 'Not required') && str_contains($body, 'Satu aturan cukup') && str_contains($bodyEn, 'One rule set is enough'));
check110('Node-RED is complementary', str_contains($body, 'Node-RED tetap otak') && str_contains($bodyEn, 'Node-RED stays the visual') && str_contains($body, 'tidak menghapus Node-RED'));
check110('no Flask or MySQL in scripts', ! str_contains($terima.$kirim.$lihat.$aturan, 'flask') && ! str_contains($terima.$kirim.$lihat.$aturan, 'mysql'));
check110('body defers Flask and MySQL', str_contains($body, 'belum Flask') && str_contains($body, 'belum MySQL') && str_contains($bodyEn, 'no Flask') && str_contains($bodyEn, 'no MySQL'));
check110('no ESP32 sketch or new wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check110('no AC mains', str_contains($body, 'Bukan AC 220V') && str_contains($bodyEn, 'Not AC mains'));
check110('ESP32 may stay on or unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check110('same lab folder as FS-39', str_contains($body, 'Documents\\fsiot-fs39') && str_contains($bodyEn, 'Documents\\fsiot-fs39'));
check110('Notepad is named before script listings', strpos($body, 'Buka dulu Notepad') < strpos($body, 'terima_stasiun.py</code>, folder') && str_contains($bodyEn, 'Open Notepad first'));
check110('File Explorer is named before folder work', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check110('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check110('cover uses the public FS-40 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs40-cover-sqlite.webp'));
check110('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check110('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check110('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check110('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check110('nine FS-40 image figures in both languages', substr_count($body, '/images/fsiot/fs40-') === 9 && substr_count($bodyEn, '/images/fsiot/fs40-') === 9);
check110('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-40)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-40)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-40)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-40)') === 2);
check110('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check110('interactive checklist is wired', str_contains($body, 'id="fsiot-sqlite-checklist"') && str_contains($body, 'id="fsiot-sqlite-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-110'") && str_contains($blade, 'initFsiotSqliteChecklist') && str_contains($langId, 'fsiot_sqlite_badge') && str_contains($langEn, 'fsiot_sqlite_badge'));
check110('ten checklist items match in both languages', checklistItems110($body) === 10 && checklistItems110($bodyEn) === 10);
check110('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-sqlite-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-sqlite-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check110('next modules are FS-41 optional and FS-42 Flask', str_contains($body, 'FS-41') && str_contains($body, 'FS-42') && str_contains($bodyEn, 'FS-41') && str_contains($bodyEn, 'FS-42') && str_contains($body, 'opsional') && str_contains($bodyEn, 'optional'));
check110('success line is locked', str_contains($terima, 'MQTT tersambung.') && str_contains($body, 'MQTT tersambung.') && str_contains($bodyEn, 'MQTT tersambung.'));
check110('Paho and SQLite docs are cited', str_contains($body, 'eclipse.dev/paho') && str_contains($body, 'docs.python.org/3/library/sqlite3.html') && str_contains($bodyEn, 'docs.python.org/3/library/sqlite3.html'));
check110('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check110('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check110('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check110('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check110('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check110('deploy hook needles for FS-40', str_contains($controller, "'#110 (ini)'") && str_contains($controller, "'paho-mqtt==2.1.0'") && str_contains($controller, "'stasiun.db'") && str_contains($controller, "'MQTT tersambung.'") && str_contains($controller, "'FS-42'"));
check110('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check110('script file names are locked', str_contains($body, 'terima_stasiun.py') && str_contains($body, 'kirim_contoh.py') && str_contains($body, 'lihat_db.py') && str_contains($bodyEn, 'terima_stasiun.py'));
check110('seo title stays within clamp', mb_strlen('Python Terima MQTT lalu Simpan ke SQLite — FS-40') <= 70 && mb_strlen('Python MQTT into SQLite for the Station — FS-40') <= 70);
preg_match("/'fsiot_sqlite_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_sqlite_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check110('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_sqlite_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check110('pass checklist copy mentions SQLite rows', str_contains($passId[1] ?? '', 'SQLite') && str_contains($passId[1] ?? '', '10'));

foreach ([
    'fs40-cover-sqlite.jpg',
    'fs40-cover-sqlite.webp',
    'fs40-tools-order.png',
    'fs40-why-python.png',
    'fs40-mqttx.png',
    'fs40-pip-venv.png',
    'fs40-callback.png',
    'fs40-script-run.png',
    'fs40-sqlite.png',
    'fs40-rules-bonus.png',
    'fs40-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check110($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$mqttxSize = getimagesize($root.'/public/images/fsiot/fs40-mqttx.png');
check110('MQTTX illustration is cropped to a readable height', $mqttxSize !== false && $mqttxSize[1] <= 800);
$runSize = getimagesize($root.'/public/images/fsiot/fs40-script-run.png');
check110('PowerShell illustration is cropped to a readable height', $runSize !== false && $runSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
