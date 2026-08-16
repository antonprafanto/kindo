<?php

/** Static quality gate for Article #114 / FS-44. Run: php scripts/audit-article114.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article114Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs44-assets.py');

$seeder = new Database\Seeders\Article114Seeder();
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

function check114(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems114(string $html): int
{
    $chunk = explode('id="fsiot-dash-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check114('draft status', str_contains($source, "'status' => 'draft'"));
check114('null publication date', str_contains($source, "'published_at' => null"));
check114('expected slug', str_contains($source, 'fullstack-iot-html-dashboard-suhu-flask'));
check114('route and controller exist', str_contains($routes, 'seed-article-114-draft') && str_contains($controller, 'seedArticle114Draft'));
check114('priority deploy and seed exist', str_contains($workflow, 'id: curl114_priority') && str_contains($workflow, 'seed-article-114-draft'));
check114('priority upload precedes FS-43 uploads', strpos($workflow, 'id: curl114_priority') < strpos($workflow, 'id: curl113_priority'));
check114('FS-44 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl114_priority.conclusion == 'success'"));
check114('late FS-44 seed is required after FTP', str_contains($workflow, 'Seed article 114 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-html-dashboard-suhu-flask'));
check114('late FS-44 seed precedes FS-43 seed', strpos($workflow, 'Seed article 114 draft via deploy hook (required, pre-launch B)') < strpos($workflow, 'Seed article 113 draft via deploy hook (required, pre-launch B)'));
check114('FS-44 images are in the priority upload', str_contains($workflow, 'fs44-cover-dashboard.webp') && str_contains($workflow, 'fs44-tools-order.png') && str_contains($workflow, 'fs44-flask-serve.png') && str_contains($workflow, 'fs44-browser-suhu.png') && str_contains($workflow, 'fs44-file-vs-http.png') && str_contains($workflow, 'fs44-troubleshooting.png'));
check114('cover is copied into public storage', str_contains($source, 'articles/covers/fs44-cover-dashboard') && str_contains($source, "Storage::disk('public')->put"));
check114('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check114('ID and EN references', str_contains($body, '#114 (ini)') && str_contains($bodyEn, '#114 (this article)'));
check114('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check114('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu File Explorer') && str_contains($body, 'Buka dulu Notepad') && str_contains($body, 'Buka dulu PowerShell') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open File Explorer first') && str_contains($bodyEn, 'Open Notepad first') && str_contains($bodyEn, 'Open PowerShell first'));
check114('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses =') && str_contains($bodyEn, 'success ='));
check114('File Explorer is opened before Python scripts', strpos($body, 'Buka File Explorer') < strpos($body, 'dashboard.html</code>, folder') && str_contains($bodyEn, 'Do not type Python commands yet'));
check114('flask version is pinned', str_contains($requirements, 'flask==3.1.3') && str_contains($body, 'flask==3.1.3') && str_contains($bodyEn, 'flask==3.1.3'));
check114('paho pin is kept in requirements', str_contains($requirements, 'paho-mqtt==2.1.0'));
check114('pip is invoked as venv python -m pip', str_contains($body, '.venv\Scripts\python.exe -m pip install -r requirements.txt') && str_contains($bodyEn, '.venv\Scripts\python.exe -m pip install -r requirements.txt'));
check114('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check114('Flask is localhost for PC scripts', str_contains($pintu, 'HOST = "127.0.0.1"') && str_contains($pintu, 'PORT = 5000') && str_contains($body, '127.0.0.1:5000'));
check114('GET reads SQLite not MariaDB', str_contains($pintu, 'stasiun.db') && str_contains($pintu, 'sqlite3.connect') && ! str_contains($pintu, 'mysql') && str_contains($body, 'stasiun.db'));
check114('Flask serves dashboard.html at GET /', str_contains($pintu, 'send_from_directory') && str_contains($pintu, 'dashboard.html') && str_contains($pintu, '@app.get("/")'));
check114('page fetch uses same-origin telemetry', str_contains($dashboard, 'fetch("/telemetry")') && str_contains($dashboard, 'Suhu tampil.') && str_contains($dashboard, 'suhu-angka'));
check114('GET still filters by device_id query', str_contains($pintu, 'request.args.get("device_id")') && str_contains($pintu, 'topic_command'));
check114('open line is locked', str_contains($pintu, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($body, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($bodyEn, 'Pintu stasiun terbuka di http://127.0.0.1:5000'));
check114('open-page line is locked', str_contains($pintu, 'Buka http://127.0.0.1:5000') && str_contains($body, 'Buka http://127.0.0.1:5000') && str_contains($bodyEn, 'Buka http://127.0.0.1:5000'));
check114('status text is locked', str_contains($body, 'Suhu tampil.') && str_contains($bodyEn, 'Suhu tampil.') && str_contains($dashboard, 'Suhu tampil.'));
check114('file protocol is forbidden as the main path', str_contains($body, 'file://') && str_contains($bodyEn, 'file://') && str_contains($body, 'Jangan buka berkas HTML lewat') && str_contains($bodyEn, 'Do not open the HTML file through'));
check114('CORS is taught without flask-cors', str_contains($body, 'CORS') && str_contains($bodyEn, 'CORS') && str_contains($body, 'flask-cors') && str_contains($body, 'Jangan pip') && ! str_contains($pintu, 'flask_cors') && ! str_contains($pintu, 'CORS'));
check114('http.server is optional not the pass gate', str_contains($body, 'Tidak wajib') && str_contains($bodyEn, 'Not required') && str_contains($body, 'python -m http.server') && str_contains($bodyEn, 'python -m http.server'));
check114('MariaDB is not required', str_contains($body, 'tidak wajib') && str_contains($bodyEn, 'not required') && str_contains($body, 'SQLite') && str_contains($bodyEn, 'SQLite'));
check114('no mysql in Flask scripts', ! str_contains($pintu.$dashboard, 'mysql') && ! str_contains($pintu, 'MariaDB'));
check114('body defers Chart.js', str_contains($body, 'belum Chart.js') && str_contains($bodyEn, 'no Chart.js') && str_contains($body, 'FS-45') && str_contains($bodyEn, 'FS-45'));
check114('no ESP32 sketch or new wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check114('no AC mains', str_contains($body, 'Bukan AC 220V') && str_contains($bodyEn, 'Not AC mains'));
check114('ESP32 may stay on or unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check114('same lab folder as FS-39', str_contains($body, 'Documents\\fsiot-fs39') && str_contains($bodyEn, 'Documents\\fsiot-fs39'));
check114('Notepad is named before script listings', strpos($body, 'Buka dulu Notepad') < strpos($body, 'dashboard.html</code>, folder') && str_contains($bodyEn, 'Open Notepad first'));
check114('File Explorer is named before folder work', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check114('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check114('cover uses the public FS-44 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs44-cover-dashboard.webp'));
check114('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check114('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check114('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check114('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check114('nine FS-44 image figures in both languages', substr_count($body, '/images/fsiot/fs44-') === 9 && substr_count($bodyEn, '/images/fsiot/fs44-') === 9);
check114('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-44)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-44)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-44)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-44)') === 2);
check114('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check114('interactive checklist is wired', str_contains($body, 'id="fsiot-dash-checklist"') && str_contains($body, 'id="fsiot-dash-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-114'") && str_contains($blade, 'initFsiotDashChecklist') && str_contains($langId, 'fsiot_dash_badge') && str_contains($langEn, 'fsiot_dash_badge'));
check114('ten checklist items match in both languages', checklistItems114($body) === 10 && checklistItems114($bodyEn) === 10);
check114('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-dash-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-dash-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check114('next module is FS-45 Chart.js', str_contains($body, 'FS-45') && str_contains($bodyEn, 'FS-45') && str_contains($body, 'Chart.js') && str_contains($bodyEn, 'Chart.js'));
check114('Flask and MDN docs are cited', str_contains($body, 'flask.palletsprojects.com') && str_contains($body, 'pypi.org/project/Flask/3.1.3') && str_contains($body, 'developer.mozilla.org') && str_contains($bodyEn, 'flask.palletsprojects.com'));
check114('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check114('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check114('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check114('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check114('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check114('deploy hook needles for FS-44', str_contains($controller, "'#114 (ini)'") && str_contains($controller, "'file://'") && str_contains($controller, "'Suhu tampil.'") && str_contains($controller, "'stasiun.db'") && str_contains($controller, "'FS-45'"));
check114('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check114('script file names are locked', str_contains($body, 'dashboard.html') && str_contains($body, 'pintu_stasiun.py') && str_contains($bodyEn, 'dashboard.html'));
check114('seo title stays within clamp', mb_strlen('Tampil Suhu di Halaman HTML — FS-44') <= 70 && mb_strlen('Show Temperature on an HTML Page — FS-44') <= 70);
preg_match("/'fsiot_dash_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_dash_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check114('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_dash_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check114('pass checklist copy mentions temperature page', str_contains($passId[1] ?? '', 'suhu') && (str_contains($passId[1] ?? '', 'HTML') || str_contains($passId[1] ?? '', 'halaman')));

foreach ([
    'fs44-cover-dashboard.jpg',
    'fs44-cover-dashboard.webp',
    'fs44-tools-order.png',
    'fs44-why-page.png',
    'fs44-file-vs-http.png',
    'fs44-origin.png',
    'fs44-flask-serve.png',
    'fs44-fetch.png',
    'fs44-browser-suhu.png',
    'fs44-address.png',
    'fs44-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check114($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$suhuSize = getimagesize($root.'/public/images/fsiot/fs44-browser-suhu.png');
check114('browser temperature illustration is cropped to a readable height', $suhuSize !== false && $suhuSize[1] <= 800);
$addressSize = getimagesize($root.'/public/images/fsiot/fs44-address.png');
check114('address-bar illustration is cropped to a readable height', $addressSize !== false && $addressSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
