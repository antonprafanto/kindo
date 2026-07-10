<?php

/**
 * Paranoid audit #20 — lapisan ketiga di luar ultra/extra/final.
 * Usage: php scripts/audit-article20-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article20Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
$href = '/artikel/' . $slug;

function check(bool $ok, string $label, bool $warning = false): void
{
    global $passed, $failed, $warn;
    if ($warning && ! $ok) {
        echo "⚠ {$label}\n";
        $warn++;

        return;
    }
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article20Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #20 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

$hookSeeders = [
    'Article20Seeder', 'Article19Seeder', 'Article18Seeder', 'Article10Seeder',
    'Article7Seeder', 'Article6Seeder', 'Article17Seeder', 'Article16Seeder',
    'RemoveDuplicateBme280Seeder',
];

foreach ($hookSeeders as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a20 = Article::where('slug', $slug)->first();
check($a20 && $a20->status === 'published', 'Artikel #20 visible setelah full chain');
check($a20?->tags()->count() === 7, 'Tag count = 7 (' . ($a20?->tags()->count() ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 18, '#10 indeks punya 18 item live (' . $indexItems . ')');
check(str_contains($a10body, 'delapan belas artikel'), '#10 teks delapan belas artikel');
check(str_contains($a10body, 'esp32-cam-streaming-mjpeg-capture-foto-wifi'), '#10 item #27 di indeks');
check(str_contains($a10body, '#28') || str_contains($a10body, 'Gateway LoRa'), '#10 teaser #28 Gateway LoRa');

echo "\n--- C: Tidak ada orphan 'Artikel #20' di seeder backlink ---\n\n";

foreach ([
    'Article6Seeder.php', 'Article7Seeder.php', 'Article10Seeder.php',
    'Article18Seeder.php', 'Article19Seeder.php',
] as $file) {
    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! preg_match('/Artikel #20/', $src), "{$file}: tidak ada teks orphan 'Artikel #20'");
}

echo "\n--- D: Outbound #20 tidak ada link mati / self-link ---\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

echo "\n--- E: Konten teknis edge-case ---\n\n";

check(str_contains($body, 'handleAPI'), 'Snippet REST handler #6');
check(str_contains($body, 'mqttClient.publish'), 'Snippet MQTT publish #7');
check(str_contains($body, 'kodingindonesia/esp32/dht22/cmd'), 'Topic kontrol terpisah');
check(str_contains($body, 'deep sleep'), 'Referensi deep sleep #11');
check(str_contains($body, 'reverse proxy'), 'Reverse proxy HTTPS');
check(str_contains($body, '8883'), 'Port MQTT TLS #17');
check(str_contains($body, 'Checklist'), 'Checklist 2 menit');

echo "\n--- F: Render HTML kritis ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'Arsitektur Hybrid'), 'Hybrid ter-render');
check(str_contains($html, 'mosquitto_sub'), 'mosquitto_sub ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 21, 'H2 ter-render ≥21 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #19 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle20\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m20);
preg_match('/function publishArticle19\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m19);
$h20 = $m20[1] ?? '';
$h19 = $m19[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h20, $needle), "Hook #20 punya {$needle}");
}

check(strpos($h20, 'Article20Seeder') !== false && strpos($h20, 'Article19Seeder') !== false && strpos($h20, 'Article20Seeder') < strpos($h20, 'Article19Seeder'), 'Urutan: seed #20 → re-seed #19');
check(strpos($deploy, 'function publishArticle19') < strpos($deploy, 'function publishArticle20'), 'DeployController: #19 hook sebelum #20 hook');

echo "\n--- H: Regresi audit batch ---\n\n";

foreach ([
    'audit-article19.php', 'audit-article18.php', 'audit-article10.php',
    'audit-article16.php', 'audit-article17.php', 'audit-article23.php',
] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

echo "\n--- I: Production state (pre-deploy #20) ---\n\n";

$prod20 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod20 === '404';
check($preDeploy, "Production #20 belum live (HTTP {$prod20}) — expected pre-deploy", ! $preDeploy);

if ($preDeploy) {
    $prod19html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'));
    $prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
    check(str_contains($prod19html, 'Artikel #20:'), 'Production #19 orphan Artikel #20 — akan fix saat deploy hook #20');
    check(! str_contains($prod19html, $href), 'Production #19 belum punya hyperlink #20 (normal pre-deploy)');
    check(str_contains($prod10html, 'empat belas artikel'), 'Production #10 masih empat belas — akan fix saat deploy hook #20');
} else {
    $prod19html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'));
    $prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
    check(str_contains($prod19html, $href), 'Production #19 hyperlink #20');
    check(str_contains($prod10html, 'delapan belas artikel') || str_contains($prod10html, 'tujuh belas artikel') || str_contains($prod10html, 'enam belas artikel'), 'Production #10 indeks artikel (tergantung deploy #27)');
    check(str_contains($prod10html, $href), 'Production #10 link #20 di indeks');
}

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'), 'TODO.md punya slug #20');
    check(str_contains($prd, '#20') || str_contains($prd, 'REST API vs MQTT'), 'PRD.md menyebut #20');
    check(str_contains($roadmap, 'rest-api-vs-mqtt'), 'roadmap punya #20');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
