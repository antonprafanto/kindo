<?php

/** Static quality gate for Article #101 / FS-31. Run: php scripts/audit-article101.php */

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';

$source = file_get_contents($root.'/database/seeders/Article101Seeder.php');
$routes = file_get_contents($root.'/routes/web.php');
$controller = file_get_contents($root.'/app/Http/Controllers/DeployController.php');
$workflow = file_get_contents($root.'/.github/workflows/deploy.yml');
$blade = file_get_contents($root.'/resources/views/articles/show.blade.php');
$langId = file_get_contents($root.'/lang/id/ui.php');
$langEn = file_get_contents($root.'/lang/en/ui.php');

$seeder = new Database\Seeders\Article101Seeder();
$reflection = new ReflectionClass($seeder);
$bodyMethod = $reflection->getMethod('body');
$bodyMethod->setAccessible(true);
$body = $bodyMethod->invoke($seeder);
$bodyEnMethod = $reflection->getMethod('bodyEn');
$bodyEnMethod->setAccessible(true);
$bodyEn = $bodyEnMethod->invoke($seeder);

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;

    echo ($ok ? 'OK   ' : 'FAIL ').$label."\n";
    $ok ? $pass++ : $fail++;
}

check('draft status', str_contains($source, "'status' => 'draft'"));
check('null publication date', str_contains($source, "'published_at' => null"));
check('expected slug', str_contains($source, 'fullstack-iot-web-server-lokal-sensor'));
check('route exists', str_contains($routes, 'seed-article-101-draft'));
check('deploy method exists', str_contains($controller, 'seedArticle101Draft'));
check('workflow seed step exists', str_contains($workflow, 'seed-article-101-draft'));
check('workflow curl101 exists', str_contains($workflow, 'id: curl101'));
check('workflow assets allowlisted', str_contains($workflow, 'fs31-local-network.png') && str_contains($workflow, 'fs31-cover-web-server.webp'));
check('deploy hook token is enforced for every public action', ! preg_match('/^    public function \w+\([^\n]*\)\R    \{\R(?!        \$this->authorizeDeployHook\(\);)/m', $controller));
check('deploy token is accepted only through the request header', str_contains($controller, "request()->header('X-Deploy-Token')") && ! str_contains($controller, "request()->query('token'"));

check('ID self reference', str_contains($body, '#101 (ini)'));
check('EN self reference', str_contains($bodyEn, '#101 (this article)'));
check('no Awam stamp in ID', ! preg_match('/\bAwam\b/u', $body));
check('no Beginner stamp in EN', ! preg_match('/\bBeginner\b/u', $bodyEn));
check('ID friendly labels', str_contains($body, 'Intinya:') && str_contains($body, 'Analogi:'));
check('EN friendly labels', str_contains($bodyEn, 'In short:') && str_contains($bodyEn, 'Analogy:'));
check('H2 parity', substr_count($body, '<h2') === substr_count($bodyEn, '<h2'));
check('at least seven figures in both languages', substr_count($body, '<figure') >= 7 && substr_count($bodyEn, '<figure') >= 7);
check('CONNECTED phase in both languages', str_contains($body, 'CONNECTED') && str_contains($bodyEn, 'CONNECTED'));
check('DHT22 and GPIO 4 in both languages', str_contains($body, 'DHT22') && str_contains($body, 'GPIO 4') && str_contains($bodyEn, 'DHT22') && str_contains($bodyEn, 'GPIO 4'));
check('WebServer sketch in both languages', str_contains($body, 'WebServer server(80)') && str_contains($bodyEn, 'WebServer server(80)'));
check('server loop in both languages', str_contains($body, 'server.handleClient') && str_contains($bodyEn, 'server.handleClient'));
check('local IP instruction in both languages', str_contains($body, 'WiFi.localIP') && str_contains($bodyEn, 'WiFi.localIP'));
check('Serial 115200 in both languages', str_contains($body, '115200') && str_contains($bodyEn, '115200'));
check('credentials placeholders in both languages', str_contains($body, 'YOUR_SSID') && str_contains($body, 'YOUR_PASS') && str_contains($bodyEn, 'YOUR_SSID') && str_contains($bodyEn, 'YOUR_PASS'));
check('test instructions in both languages', str_contains($body, 'Cara menguji perintah di atas') && str_contains($bodyEn, 'How to test the commands above'));
check('Library Manager directions in both languages', str_contains($body, 'Library Manager') && str_contains($bodyEn, 'Library Manager'));
check('Wi-Fi dots recovery in both languages', str_contains($body, 'hanya muncul titik') && str_contains($bodyEn, 'only dots appear'));
check('Wi-Fi correction requires Upload in both languages', str_contains($body, 'lalu tekan <strong>Upload</strong> lagi') && str_contains($bodyEn, 'then press <strong>Upload</strong> again'));
check('http rather than https warning in both languages', str_contains($body, 'bukan</strong> <code>https://</code>') && str_contains($bodyEn, 'not</strong> <code>https://</code>'));
check('Serial Monitor tool path and official source in both languages', str_contains($body, 'Klik menu <strong>Tools</strong>') && str_contains($bodyEn, 'Click the <strong>Tools</strong> menu') && str_contains($body, 'ide-v2-serial-monitor') && str_contains($bodyEn, 'ide-v2-serial-monitor'));
check('no Laragon instruction', str_contains($body, 'Laragon') && str_contains($bodyEn, 'Laragon'));
check('security boundary in both languages', str_contains($body, 'port forwarding') && str_contains($bodyEn, 'port-forward'));
check('localhost warning in both languages', str_contains($body, 'localhost') && str_contains($bodyEn, 'localhost'));
check('main and helper figure labels', str_contains($body, 'Gambar utama') && str_contains($body, 'Skema bantu') && str_contains($bodyEn, 'Main figure') && str_contains($bodyEn, 'Helper schematic'));
check('official Espressif source cited', str_contains($body, 'github.com/espressif/arduino-esp32') && str_contains($bodyEn, 'github.com/espressif/arduino-esp32'));
check('MDN source cited', str_contains($body, 'developer.mozilla.org') && str_contains($bodyEn, 'developer.mozilla.org'));
check('Adafruit sensor source cited', str_contains($body, 'learn.adafruit.com/dht') && str_contains($bodyEn, 'learn.adafruit.com/dht'));
check('checklist IDs survive sanitizer', str_contains($body, 'id="fsiot-webserver-checklist"') && str_contains($body, 'id="fsiot-webserver-checklist-items"'));
check('ten ID checklist items', substr_count(explode('id="fsiot-webserver-checklist-items"', $body)[1] ?? '', '<li>') >= 10);
check('ten EN checklist items', substr_count(explode('id="fsiot-webserver-checklist-items"', $bodyEn)[1] ?? '', '<li>') >= 10);
check('interactive checklist wired', str_contains($blade, 'initFsiotWebServerChecklist') && str_contains($blade, 'fsiot-cl-101'));
check('checklist keeps local how-to visible', str_contains($blade, 'Cara menguji|How to test'));
check('ID checklist translations', str_contains($langId, 'fsiot_webserver_badge'));
check('EN checklist translations', str_contains($langEn, 'fsiot_webserver_badge'));
check('course page link in both languages', str_contains($body, '/belajar/fullstack-iot') && str_contains($bodyEn, '/belajar/fullstack-iot'));
check('no hard links to draft FSIOT articles', ! preg_match('#/artikel/fullstack-iot-[a-z0-9-]+#', $body.$bodyEn));

foreach (['fs31-cover-web-server.jpg', 'fs31-cover-web-server.webp', 'fs31-tools-order.png', 'fs31-webserver-core.png', 'fs31-local-network.png', 'fs31-refresh-flow.png', 'fs31-success-browser.png', 'fs31-troubleshooting.png'] as $asset) {
    check($asset.' exists', is_file($root.'/public/images/fsiot/'.$asset));
}

echo "\n{$pass} pass / {$fail} fail\n";
exit($fail === 0 ? 0 : 1);
