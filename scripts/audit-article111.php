<?php

/** Static quality gate for Article #111 / FS-41. Run: php scripts/audit-article111.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article111Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs41-assets.py');

$seeder = new Database\Seeders\Article111Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);
$salinMethod = $reflection->getMethod('salin');
$salinMethod->setAccessible(true);
$salin = $salinMethod->invoke($seeder);
$lihatMethod = $reflection->getMethod('lihat');
$lihatMethod->setAccessible(true);
$lihat = $lihatMethod->invoke($seeder);
$contohMethod = $reflection->getMethod('contoh');
$contohMethod->setAccessible(true);
$contoh = $contohMethod->invoke($seeder);
$reqMethod = $reflection->getMethod('requirements');
$reqMethod->setAccessible(true);
$requirements = $reqMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check111(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems111(string $html): int
{
    $chunk = explode('id="fsiot-mysql-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check111('draft status', str_contains($source, "'status' => 'draft'"));
check111('null publication date', str_contains($source, "'published_at' => null"));
check111('expected slug', str_contains($source, 'fullstack-iot-mariadb-histori-sqlite-stasiun'));
check111('route and controller exist', str_contains($routes, 'seed-article-111-draft') && str_contains($controller, 'seedArticle111Draft'));
check111('priority deploy and seed exist', str_contains($workflow, 'id: curl111_priority') && str_contains($workflow, 'seed-article-111-draft'));
check111('priority upload precedes FS-40 uploads', strpos($workflow, 'id: curl111_priority') < strpos($workflow, 'id: curl110_priority'));
check111('FS-41 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl111_priority.conclusion == 'success'"));
check111('late FS-41 seed is required after FTP', str_contains($workflow, 'Seed article 111 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-mariadb-histori-sqlite-stasiun'));
check111('FS-41 images are in the priority upload', str_contains($workflow, 'fs41-cover-mysql.webp') && str_contains($workflow, 'fs41-tools-order.png') && str_contains($workflow, 'fs41-copy-flow.png') && str_contains($workflow, 'fs41-select.png') && str_contains($workflow, 'fs41-troubleshooting.png'));
check111('cover is copied into public storage', str_contains($source, 'articles/covers/fs41-cover-mysql') && str_contains($source, "Storage::disk('public')->put"));
check111('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check111('ID and EN references', str_contains($body, '#111 (ini)') && str_contains($bodyEn, '#111 (this article)'));
check111('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check111('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu XAMPP Control Panel') && str_contains($body, 'Buka dulu Notepad') && str_contains($body, 'Buka dulu PowerShell') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open XAMPP Control Panel first') && str_contains($bodyEn, 'Open Notepad first') && str_contains($bodyEn, 'Open PowerShell first'));
check111('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses =') && str_contains($bodyEn, 'success ='));
check111('XAMPP is opened before pip', strpos($body, 'Buka XAMPP') < strpos($body, 'pip install -r requirements.txt') && str_contains($bodyEn, 'Do not type pip yet'));
check111('connector version is pinned', str_contains($requirements, 'mysql-connector-python==26.7.0') && str_contains($body, 'mysql-connector-python==26.7.0') && str_contains($bodyEn, 'mysql-connector-python==26.7.0'));
check111('paho pin is kept in requirements', str_contains($requirements, 'paho-mqtt==2.1.0'));
check111('pip is invoked as venv python -m pip', str_contains($body, '.venv\Scripts\python.exe -m pip install -r requirements.txt') && str_contains($bodyEn, '.venv\Scripts\python.exe -m pip install -r requirements.txt'));
check111('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check111('MariaDB is localhost for PC scripts', str_contains($salin, 'HOST = "127.0.0.1"') && str_contains($salin, 'PORT = 3306') && str_contains($body, '127.0.0.1'));
check111('database and table names are locked', str_contains($salin, 'DATABASE = "stasiun"') && str_contains($salin, 'CREATE TABLE IF NOT EXISTS telemetry') && str_contains($body, 'stasiun.db'));
check111('copy script reads SQLite then inserts MySQL', str_contains($salin, 'stasiun.db') && str_contains($salin, 'DELETE FROM telemetry') && str_contains($salin, 'mysql.connector.connect'));
check111('lihat reads MariaDB', str_contains($lihat, 'database=DATABASE') && str_contains($lihat, 'Jumlah baris:'));
check111('copy success line is locked', str_contains($salin, '10 baris tersalin ke MariaDB.') && str_contains($body, '10 baris tersalin ke MariaDB.') && str_contains($bodyEn, '10 baris tersalin ke MariaDB.'));
check111('bonus sample insert is optional', str_contains($body, 'Tidak wajib') && str_contains($bodyEn, 'Not required') && str_contains($contoh, 'range(10)') && str_contains($contoh, '10 baris contoh masuk MariaDB.'));
check111('module is optional and SQLite stays main path', str_contains($body, 'opsional') && str_contains($bodyEn, 'optional') && str_contains($body, 'SQLite tetap') && str_contains($bodyEn, 'SQLite stays'));
check111('no Flask in scripts', ! str_contains($salin.$lihat.$contoh, 'flask') && ! str_contains($salin.$lihat.$contoh, 'Flask'));
check111('body defers Flask', str_contains($body, 'belum Flask') && str_contains($bodyEn, 'no Flask'));
check111('no ESP32 sketch or new wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check111('no AC mains', str_contains($body, 'Bukan AC 220V') && str_contains($bodyEn, 'Not AC mains'));
check111('ESP32 may stay on or unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check111('same lab folder as FS-39', str_contains($body, 'Documents\\fsiot-fs39') && str_contains($bodyEn, 'Documents\\fsiot-fs39'));
check111('Notepad is named before script listings', strpos($body, 'Buka dulu Notepad') < strpos($body, 'salin_ke_mysql.py</code>, folder') && str_contains($bodyEn, 'Open Notepad first'));
check111('File Explorer is named before folder work', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check111('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check111('cover uses the public FS-41 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs41-cover-mysql.webp'));
check111('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check111('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check111('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check111('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check111('nine FS-41 image figures in both languages', substr_count($body, '/images/fsiot/fs41-') === 9 && substr_count($bodyEn, '/images/fsiot/fs41-') === 9);
check111('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-41)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-41)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-41)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-41)') === 2);
check111('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check111('interactive checklist is wired', str_contains($body, 'id="fsiot-mysql-checklist"') && str_contains($body, 'id="fsiot-mysql-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-111'") && str_contains($blade, 'initFsiotMysqlChecklist') && str_contains($langId, 'fsiot_mysql_badge') && str_contains($langEn, 'fsiot_mysql_badge'));
check111('ten checklist items match in both languages', checklistItems111($body) === 10 && checklistItems111($bodyEn) === 10);
check111('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-mysql-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-mysql-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check111('next module is FS-42 Flask', str_contains($body, 'FS-42') && str_contains($bodyEn, 'FS-42') && str_contains($body, 'Flask') && str_contains($bodyEn, 'Flask'));
check111('XAMPP and connector docs are cited', str_contains($body, 'apachefriends.org') && str_contains($body, 'dev.mysql.com/doc/connector-python') && str_contains($bodyEn, 'apachefriends.org'));
check111('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check111('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check111('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check111('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check111('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check111('deploy hook needles for FS-41', str_contains($controller, "'#111 (ini)'") && str_contains($controller, "'mysql-connector-python==26.7.0'") && str_contains($controller, "'stasiun.db'") && str_contains($controller, "'Jumlah baris: 10'") && str_contains($controller, "'FS-42'"));
check111('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check111('script file names are locked', str_contains($body, 'salin_ke_mysql.py') && str_contains($body, 'lihat_mysql.py') && str_contains($bodyEn, 'salin_ke_mysql.py'));
check111('seo title stays within clamp', mb_strlen('Salin Histori SQLite ke MariaDB di PC — FS-41') <= 70 && mb_strlen('Copy SQLite History into MariaDB on the PC — FS-41') <= 70);
preg_match("/'fsiot_mysql_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_mysql_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check111('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_mysql_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check111('pass checklist copy mentions MariaDB rows', str_contains($passId[1] ?? '', 'MariaDB') && str_contains($passId[1] ?? '', '10'));

foreach ([
    'fs41-cover-mysql.jpg',
    'fs41-cover-mysql.webp',
    'fs41-tools-order.png',
    'fs41-why-mysql.png',
    'fs41-download.png',
    'fs41-xampp.png',
    'fs41-phpmyadmin.png',
    'fs41-pip-venv.png',
    'fs41-copy-flow.png',
    'fs41-select.png',
    'fs41-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check111($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$xamppSize = getimagesize($root.'/public/images/fsiot/fs41-xampp.png');
check111('XAMPP illustration is cropped to a readable height', $xamppSize !== false && $xamppSize[1] <= 800);
$phpSize = getimagesize($root.'/public/images/fsiot/fs41-phpmyadmin.png');
check111('phpMyAdmin illustration is cropped to a readable height', $phpSize !== false && $phpSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
