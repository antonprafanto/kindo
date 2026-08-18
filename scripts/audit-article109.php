<?php

/** Static quality gate for Article #109 / FS-39. Run: php scripts/audit-article109.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article109Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs39-assets.py');

$seeder = new Database\Seeders\Article109Seeder();
$reflection = new ReflectionClass($seeder);
$idMethod = $reflection->getMethod('body');
$idMethod->setAccessible(true);
$body = $idMethod->invoke($seeder);
$enMethod = $reflection->getMethod('bodyEn');
$enMethod->setAccessible(true);
$bodyEn = $enMethod->invoke($seeder);
$scriptMethod = $reflection->getMethod('script');
$scriptMethod->setAccessible(true);
$script = $scriptMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check109(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems109(string $html): int
{
    $chunk = explode('id="fsiot-python-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check109('draft status', str_contains($source, "'status' => 'draft'"));
check109('null publication date', str_contains($source, "'published_at' => null"));
check109('expected slug', str_contains($source, 'fullstack-iot-python-dari-nol-script-pertama'));
preg_match("/'seo_title'\\s*=>\\s*'([^']*)'/", $source, $seoTitleId);
preg_match("/'seo_title_en'\\s*=>\\s*'([^']*)'/", $source, $seoTitleEn);
check109('seo_title keeps FS-39 and stays ≤70', str_contains($seoTitleId[1] ?? '', 'FS-39') && mb_strlen($seoTitleId[1] ?? '') <= 70);
check109('seo_title_en keeps FS-39 and stays ≤70', str_contains($seoTitleEn[1] ?? '', 'FS-39') && mb_strlen($seoTitleEn[1] ?? '') <= 70);
check109('route and controller exist', str_contains($routes, 'seed-article-109-draft') && str_contains($controller, 'seedArticle109Draft'));
check109('priority deploy and seed exist', str_contains($workflow, 'id: curl109_priority') && str_contains($workflow, 'seed-article-109-draft'));
check109('priority upload precedes FS-38 uploads', strpos($workflow, 'id: curl109_priority') < strpos($workflow, 'id: curl108_priority'));
check109('FS-39 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl109_priority.conclusion == 'success'"));
check109('late FS-39 seed is required after FTP', str_contains($workflow, 'Seed article 109 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-python-dari-nol-script-pertama'));
check109('FS-39 images are in the priority upload', str_contains($workflow, 'fs39-cover-python.webp') && str_contains($workflow, 'fs39-tools-order.png') && str_contains($workflow, 'fs39-installer-path.png') && str_contains($workflow, 'fs39-venv.png') && str_contains($workflow, 'fs39-script-run.png') && str_contains($workflow, 'fs39-troubleshooting.png'));
check109('cover is copied into public storage', str_contains($source, 'articles/covers/fs39-cover-python') && str_contains($source, "Storage::disk('public')->put"));
check109('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check109('ID and EN references', str_contains($body, '#109 (ini)') && str_contains($bodyEn, '#109 (this article)'));
check109('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check109('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu PowerShell') && str_contains($body, 'Buka dulu Notepad') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open PowerShell first') && str_contains($bodyEn, 'Open Notepad first'));
check109('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses = PowerShell menampilkan versi') && str_contains($bodyEn, 'success = PowerShell shows version'));
check109('python.org is opened before python commands', str_contains($body, 'python.org/downloads') && strpos($body, 'python.org/downloads') < strpos($body, '<pre><code>python --version') && str_contains($bodyEn, 'python.org/downloads'));
check109('PATH checkbox is taught before version check', str_contains($body, 'Add python.exe to PATH') && strpos($body, 'Add python.exe to PATH') < strpos($body, '<pre><code>python --version') && str_contains($bodyEn, 'Add python.exe to PATH'));
check109('pip is invoked as python -m pip', str_contains($body, 'python -m pip --version') && str_contains($bodyEn, 'python -m pip --version'));
check109('venv is created with python -m venv', str_contains($body, 'python -m venv .venv') && str_contains($bodyEn, 'python -m venv .venv'));
check109('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check109('Microsoft Store is rejected without error-screen phrasing', str_contains($body, 'Microsoft Store') && str_contains($body, 'pemasang yang salah') && str_contains($bodyEn, 'Microsoft Store') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check109('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen'));
check109('Python docs are cited', str_contains($body, 'docs.python.org/3/using/windows.html') && str_contains($body, 'docs.python.org/3/tutorial/venv.html') && str_contains($bodyEn, 'docs.python.org/3/using/windows.html'));
check109('minimum version is 3.11', str_contains($body, '3.11') && str_contains($source, 'Python 3.11'));
check109('success line is locked', str_contains($script, 'Siap terima data stasiun') && str_contains($body, 'Siap terima data stasiun') && str_contains($bodyEn, 'Siap terima data stasiun'));
check109('script reads a simple argument', str_contains($script, 'sys.argv') && str_contains($script, 'stasiun-meja-01') && str_contains($body, 'esp32-meja-01'));
check109('MQTT and paho wait for FS-40', str_contains($body, 'paho-mqtt') && str_contains($body, 'FS-40') && ! str_contains($script, 'paho') && ! str_contains($script, 'mqtt') && ! str_contains($script, 'sqlite'));
check109('no ESP32 sketch or new wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check109('ESP32 may be unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check109('Notepad is named before the script listing', strpos($body, 'Buka dulu Notepad') < strpos($body, 'import sys') && str_contains($bodyEn, 'Open Notepad first'));
check109('File Explorer is named before mkdir commands', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check109('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check109('cover uses the public FS-39 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs39-cover-python.webp'));
check109('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check109('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check109('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check109('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check109('nine FS-39 image figures in both languages', substr_count($body, '/images/fsiot/fs39-') === 9 && substr_count($bodyEn, '/images/fsiot/fs39-') === 9);
check109('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-39)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-39)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-39)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-39)') === 2);
check109('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check109('interactive checklist is wired', str_contains($body, 'id="fsiot-python-checklist"') && str_contains($body, 'id="fsiot-python-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-109'") && str_contains($blade, 'initFsiotPythonChecklist') && str_contains($langId, 'fsiot_python_badge') && str_contains($langEn, 'fsiot_python_badge'));
check109('ten checklist items match in both languages', checklistItems109($body) === 10 && checklistItems109($bodyEn) === 10);
check109('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-python-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-python-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check109('next module is FS-40 MQTT Python', str_contains($body, 'FS-40') && str_contains($bodyEn, 'FS-40') && str_contains($body, 'berlangganan MQTT') && str_contains($bodyEn, 'subscribes to MQTT'));
check109('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check109('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check109('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check109('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check109('deploy hook needles for FS-39', str_contains($controller, "'#109 (ini)'") && str_contains($controller, "'Siap terima data stasiun'") && str_contains($controller, "'siap_stasiun.py'") && str_contains($controller, "'FS-40'"));
check109('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check109('script file name is siap_stasiun.py', str_contains($body, 'siap_stasiun.py') && str_contains($bodyEn, 'siap_stasiun.py'));
preg_match("/'fsiot_python_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_python_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check109('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_python_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check109('pass checklist copy mentions Python on the PC', str_contains($passId[1] ?? '', 'Python') && str_contains($passId[1] ?? '', 'stasiun'));

foreach ([
    'fs39-cover-python.jpg',
    'fs39-cover-python.webp',
    'fs39-tools-order.png',
    'fs39-why-pc.png',
    'fs39-download.png',
    'fs39-installer-path.png',
    'fs39-version-ok.png',
    'fs39-venv.png',
    'fs39-script-run.png',
    'fs39-argv.png',
    'fs39-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check109($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$downloadSize = getimagesize($root.'/public/images/fsiot/fs39-download.png');
check109('download illustration is cropped to a readable height', $downloadSize !== false && $downloadSize[1] <= 800);
$versionSize = getimagesize($root.'/public/images/fsiot/fs39-version-ok.png');
check109('PowerShell illustration is cropped to a readable height', $versionSize !== false && $versionSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
