<?php

/** Static quality gate for Article #115 / FS-45. Run: php scripts/audit-article115.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article115Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');
$assets = file_get_contents($root.'/scripts/gen-fs45-assets.py');

$seeder = new Database\Seeders\Article115Seeder();
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
$isiMethod = $reflection->getMethod('isi');
$isiMethod->setAccessible(true);
$isi = $isiMethod->invoke($seeder);
$reqMethod = $reflection->getMethod('requirements');
$reqMethod->setAccessible(true);
$requirements = $reqMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check115(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

function checklistItems115(string $html): int
{
    $chunk = explode('id="fsiot-chart-checklist-items"', $html)[1] ?? '';
    $chunk = explode('</ul>', $chunk, 2)[0] ?? '';

    return substr_count($chunk, '<li>');
}

check115('draft status', str_contains($source, "'status' => 'draft'"));
check115('null publication date', str_contains($source, "'published_at' => null"));
check115('expected slug', str_contains($source, 'fullstack-iot-chartjs-histori-suhu-flask'));
check115('route and controller exist', str_contains($routes, 'seed-article-115-draft') && str_contains($controller, 'seedArticle115Draft'));
check115('priority deploy and seed exist', str_contains($workflow, 'id: curl115_priority') && str_contains($workflow, 'seed-article-115-draft'));
check115('priority upload precedes FS-44 uploads', strpos($workflow, 'id: curl115_priority') < strpos($workflow, 'id: curl114_priority'));
check115('FS-45 seed is enabled after priority upload', str_contains($workflow, "if: always() && !cancelled() && steps.curl115_priority.conclusion == 'success'"));
check115('late FS-45 seed is required after FTP', str_contains($workflow, 'Seed article 115 draft via deploy hook (required, pre-launch B)') && str_contains($workflow, 'fullstack-iot-chartjs-histori-suhu-flask'));
check115('late FS-45 seed precedes FS-44 seed', strpos($workflow, 'Seed article 115 draft via deploy hook (required, pre-launch B)') < strpos($workflow, 'Seed article 114 draft via deploy hook (required, pre-launch B)'));
check115('FS-45 images are in the priority upload', str_contains($workflow, 'fs45-cover-chart.webp') && str_contains($workflow, 'fs45-tools-order.png') && str_contains($workflow, 'fs45-flask-serve.png') && str_contains($workflow, 'fs45-browser-chart.png') && str_contains($workflow, 'fs45-history.png') && str_contains($workflow, 'fs45-troubleshooting.png'));
check115('cover is copied into public storage', str_contains($source, 'articles/covers/fs45-cover-chart') && str_contains($source, "Storage::disk('public')->put"));
check115('trashed slug is restored', str_contains($source, 'withTrashed()') && str_contains($source, 'restore()'));
check115('ID and EN references', str_contains($body, '#115 (ini)') && str_contains($bodyEn, '#115 (this article)'));
check115('friendly opening labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:') && str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check115('tools-first instructions', str_contains($body, 'Buka browser') && str_contains($body, 'Buka dulu File Explorer') && str_contains($body, 'Buka dulu Notepad') && str_contains($body, 'Buka dulu PowerShell') && str_contains($bodyEn, 'Open a browser') && str_contains($bodyEn, 'Open File Explorer first') && str_contains($bodyEn, 'Open Notepad first') && str_contains($bodyEn, 'Open PowerShell first'));
check115('numbered install cards exist', str_contains($body, 'list-style:none') && str_contains($body, 'bukti sukses =') && str_contains($bodyEn, 'success ='));
check115('File Explorer is opened before Python scripts', strpos($body, 'Buka File Explorer') < strpos($body, '.venv\Scripts\python.exe isi_histori.py') && str_contains($bodyEn, 'Do not type Python commands yet'));
check115('flask version is pinned', str_contains($requirements, 'flask==3.1.3') && str_contains($body, 'flask==3.1.3') && str_contains($bodyEn, 'flask==3.1.3'));
check115('paho pin is kept in requirements', str_contains($requirements, 'paho-mqtt==2.1.0'));
check115('pip is invoked as venv python -m pip', str_contains($body, '.venv\Scripts\python.exe -m pip install -r requirements.txt') && str_contains($bodyEn, '.venv\Scripts\python.exe -m pip install -r requirements.txt'));
check115('do not change ExecutionPolicy', str_contains($body, 'jangan ubah ExecutionPolicy') && str_contains($bodyEn, 'do not change ExecutionPolicy'));
check115('Flask is localhost for PC scripts', str_contains($pintu, 'HOST = "127.0.0.1"') && str_contains($pintu, 'PORT = 5000') && str_contains($body, '127.0.0.1:5000'));
check115('GET reads SQLite not MariaDB', str_contains($pintu, 'stasiun.db') && str_contains($pintu, 'sqlite3.connect') && ! str_contains($pintu, 'mysql') && str_contains($body, 'stasiun.db'));
check115('Flask serves dashboard.html at GET /', str_contains($pintu, 'send_from_directory') && str_contains($pintu, 'dashboard.html') && str_contains($pintu, '@app.get("/")'));
check115('history endpoint is one hour and capped', str_contains($pintu, '@app.get("/history")') && str_contains($pintu, 'timedelta(hours=1)') && str_contains($pintu, 'MAX_POINTS = 60'));
check115('page fetch uses same-origin history', str_contains($dashboard, 'fetch("/history?hours=1")') && str_contains($dashboard, 'Grafik tampil.') && str_contains($dashboard, 'grafik-suhu'));
check115('Chart.js is pinned CDN not npm', str_contains($dashboard, 'cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js') && str_contains($body, 'chart.js@4.4.1') && str_contains($body, 'jsDelivr') && ! str_contains($dashboard, 'npm install'));
check115('polling is five seconds', str_contains($dashboard, 'setInterval(muat, 5000)') && str_contains($body, '5 detik') && str_contains($bodyEn, '5 seconds'));
check115('isi_histori prints locked line', str_contains($isi, '12 titik satu jam siap.') && str_contains($body, '12 titik satu jam siap.') && str_contains($bodyEn, '12 titik satu jam siap.'));
check115('GET still filters by device_id query', str_contains($pintu, 'request.args.get("device_id")') && str_contains($pintu, 'topic_command'));
check115('open line is locked', str_contains($pintu, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($body, 'Pintu stasiun terbuka di http://127.0.0.1:5000') && str_contains($bodyEn, 'Pintu stasiun terbuka di http://127.0.0.1:5000'));
check115('open-page line is locked', str_contains($pintu, 'Buka http://127.0.0.1:5000') && str_contains($body, 'Buka http://127.0.0.1:5000') && str_contains($bodyEn, 'Buka http://127.0.0.1:5000'));
check115('history print line is locked', str_contains($pintu, 'GET  http://127.0.0.1:5000/history?hours=1'));
check115('status text is locked', str_contains($body, 'Grafik tampil.') && str_contains($bodyEn, 'Grafik tampil.') && str_contains($dashboard, 'Grafik tampil.'));
check115('file protocol is forbidden as the main path', str_contains($body, 'file://') && str_contains($bodyEn, 'file://') && str_contains($body, 'Jangan buka berkas HTML lewat') && str_contains($bodyEn, 'Do not open the HTML file through'));
check115('CORS library is not the pass gate', str_contains($body, 'flask-cors') && str_contains($body, 'Jangan') && ! str_contains($pintu, 'flask_cors') && ! str_contains($pintu, 'CORS'));
check115('MariaDB is not required', str_contains($body, 'tidak wajib') && str_contains($bodyEn, 'not required') && str_contains($body, 'SQLite') && str_contains($bodyEn, 'SQLite'));
check115('no mysql in Flask scripts', ! str_contains($pintu.$dashboard.$isi, 'mysql') && ! str_contains($pintu, 'MariaDB'));
check115('ON/OFF UI is deferred to FS-46', str_contains($body, 'tombol ON/OFF') && str_contains($bodyEn, 'ON/OFF buttons') && str_contains($body, 'FS-46') && str_contains($bodyEn, 'FS-46'));
check115('time axis uses received_at not millis', str_contains($body, 'received_at') && str_contains($body, 'millis') && str_contains($dashboard, 'jamLabel') && str_contains($bodyEn, 'millis'));
check115('no ESP32 sketch or new wiring', ! str_contains($source, '#include') && ! str_contains($body, 'GPIO 4') && ! str_contains($body, 'GPIO 26'));
check115('no AC mains', str_contains($body, 'Bukan AC 220V') && str_contains($bodyEn, 'Not AC mains'));
check115('ESP32 may stay on or unplugged', str_contains($body, 'boleh dicabut') && str_contains($bodyEn, 'may be unplugged'));
check115('same lab folder as FS-39', str_contains($body, 'Documents\\fsiot-fs39') && str_contains($bodyEn, 'Documents\\fsiot-fs39'));
check115('Notepad is named before script listings', strpos($body, 'Buka dulu Notepad') < strpos($body, 'isi_histori.py</code>, folder') && str_contains($bodyEn, 'Open Notepad first'));
check115('File Explorer is named before folder work', str_contains($body, 'Buka dulu File Explorer') && str_contains($bodyEn, 'Open File Explorer first'));
check115('data-flow figure is left to right', str_contains($body, 'Baca dari kiri ke kanan') && str_contains($bodyEn, 'Read left to right'));
check115('cover uses the public FS-45 asset', str_contains($source, 'https://kodingindonesia.com/images/fsiot/fs45-cover-chart.webp'));
check115('article view supports absolute cover URLs', str_contains($blade, "str_starts_with(\$article->cover_image, 'http')"));
check115('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2') && substr_count($body, '<h2') >= 16);
check115('glossary heading exists in both languages', str_contains($body, 'Istilah yang dipakai hari ini') && str_contains($bodyEn, 'Terms used today'));
check115('FAQ and sources headings exist', str_contains($body, 'Pertanyaan yang sering muncul') && str_contains($body, '>Sumber<') && str_contains($bodyEn, 'Frequently asked questions') && str_contains($bodyEn, '>Sources<'));
check115('nine FS-45 image figures in both languages', substr_count($body, '/images/fsiot/fs45-') === 9 && substr_count($bodyEn, '/images/fsiot/fs45-') === 9);
check115('Koding Indonesia diagrams attributed', substr_count($body, 'Diagram buatan Koding Indonesia (FS-45)') === 7 && substr_count($body, 'Ilustrasi buatan Koding Indonesia (FS-45)') === 2 && substr_count($bodyEn, 'Diagram by Koding Indonesia (FS-45)') === 7 && substr_count($bodyEn, 'Illustration by Koding Indonesia (FS-45)') === 2);
check115('phone zoom tip exists', str_contains($body, 'Tips ponsel') && str_contains($bodyEn, 'Phone tip'));
check115('interactive checklist is wired', str_contains($body, 'id="fsiot-chart-checklist"') && str_contains($body, 'id="fsiot-chart-checklist-items"') && str_contains($blade, "storagePrefix: 'fsiot-cl-115'") && str_contains($blade, 'initFsiotChartChecklist') && str_contains($langId, 'fsiot_chart_badge') && str_contains($langEn, 'fsiot_chart_badge'));
check115('ten checklist items match in both languages', checklistItems115($body) === 10 && checklistItems115($bodyEn) === 10);
check115('one paper list in each checklist section', substr_count(explode('<h2>', explode('id="fsiot-chart-checklist"', $body, 2)[1] ?? '', 2)[0], '<ul') === 1 && substr_count(explode('<h2>', explode('id="fsiot-chart-checklist"', $bodyEn, 2)[1] ?? '', 2)[0], '<ul') === 1);
check115('next module is FS-46 ON/OFF', str_contains($body, 'FS-46') && str_contains($bodyEn, 'FS-46') && str_contains($body, 'ON/OFF') && str_contains($bodyEn, 'ON/OFF'));
check115('Chart.js and Flask docs are cited', str_contains($body, 'chartjs.org') && str_contains($body, 'jsdelivr.com/package/npm/chart.js') && str_contains($body, 'flask.palletsprojects.com') && str_contains($body, 'pypi.org/project/Flask/3.1.3') && str_contains($bodyEn, 'chartjs.org'));
check115('EYD avoids sungguhan', ! str_contains($body, 'sungguhan'));
check115('diagram warning copy is a lab note not an error banner', str_contains($assets, 'Catatan lab:') && str_contains($assets, "'#b45309'") && ! str_contains($assets, "'#b91c1c'"));
check115('no Awam or Beginner stamps', ! preg_match('/\bAwam:|\bBeginner:/', $body.$bodyEn));
check115('no success-screen error phrasing', ! str_contains($body, 'Ini tampilan yang benar, bukan layar error') && ! str_contains($bodyEn, 'This is the correct view, not an error screen') && ! str_contains($body, 'bukan layar error') && ! str_contains($bodyEn, 'not an error screen'));
check115('no hard links to draft articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));
check115('deploy hook needles for FS-45', str_contains($controller, "'#115 (ini)'") && str_contains($controller, "'Grafik tampil.'") && str_contains($controller, "'/history?hours=1'") && str_contains($controller, "'12 titik satu jam siap.'") && str_contains($controller, "'FS-46'"));
check115('tools diagram matches five install cards', str_contains($body, 'lima langkah') && str_contains($bodyEn, 'five steps'));
check115('script file names are locked', str_contains($body, 'isi_histori.py') && str_contains($body, 'dashboard.html') && str_contains($body, 'pintu_stasiun.py') && str_contains($bodyEn, 'isi_histori.py'));
check115('seo title stays within clamp', mb_strlen('Grafik Tren Suhu — FS-45') <= 70 && mb_strlen('Show a Temperature Trend Chart — FS-45') <= 70);
preg_match("/'fsiot_chart_incomplete'\\s*=>\\s*'([^']*)'/", $langId, $incompleteId);
preg_match("/'fsiot_chart_incomplete'\\s*=>\\s*'([^']*)'/", $langEn, $incompleteEn);
check115('incomplete checklist copy has no remaining placeholder', ($incompleteId[1] ?? '') !== '' && ! str_contains($incompleteId[1], ':remaining') && ($incompleteEn[1] ?? '') !== '' && ! str_contains($incompleteEn[1], ':remaining'));
preg_match("/'fsiot_chart_pass'\\s*=>\\s*'([^']*)'/", $langId, $passId);
check115('pass checklist copy mentions the chart', str_contains($passId[1] ?? '', 'Grafik tampil') || str_contains($passId[1] ?? '', 'grafik'));

foreach ([
    'fs45-cover-chart.jpg',
    'fs45-cover-chart.webp',
    'fs45-tools-order.png',
    'fs45-why-chart.png',
    'fs45-history.png',
    'fs45-cdn.png',
    'fs45-flask-serve.png',
    'fs45-polling.png',
    'fs45-browser-chart.png',
    'fs45-time-axis.png',
    'fs45-troubleshooting.png',
] as $asset) {
    $path = $root.'/public/images/fsiot/'.$asset;
    $dimensions = is_file($path) ? getimagesize($path) : false;
    check115($asset.' exists and is readable', $dimensions !== false && $dimensions[0] >= 1000 && $dimensions[1] >= 600);
}

$chartSize = getimagesize($root.'/public/images/fsiot/fs45-browser-chart.png');
check115('browser chart illustration is cropped to a readable height', $chartSize !== false && $chartSize[1] <= 800);
$timeSize = getimagesize($root.'/public/images/fsiot/fs45-time-axis.png');
check115('time-axis illustration is cropped to a readable height', $timeSize !== false && $timeSize[1] <= 800);

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
