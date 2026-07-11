<?php

/**
 * Paranoid audit #32 — lapisan ketiga.
 * Usage: php scripts/audit-article32-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article32Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'bluetooth-esp32-ble-kirim-data-sensor-smartphone';
$slug31 = 'freertos-esp32-multi-task-sensor-wifi-mqtt';

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

$ref = new ReflectionClass(Article32Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #32 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

foreach ([
    'Article32Seeder', 'Article31Seeder', 'Article10Seeder',
    'PatchArticle1BluetoothSeeder', 'RemoveDuplicateBme280Seeder',
] as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a32 = Article::where('slug', $slug)->first();
check($a32 && $a32->status === 'published', 'Artikel #32 visible setelah full chain');
check($a32?->tags()->count() === 6, 'Tag count = 6 (' . ($a32?->tags()->count() ?? 0) . ')');
check($a32?->read_time_minutes >= 8, 'read_time ≥ 8 menit (' . ($a32?->read_time_minutes ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 23, '#10 indeks punya 23 item live (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh tiga artikel'), '#10 teks dua puluh tiga artikel');
check(str_contains($a10body, $slug), '#10 item #32 di indeks');
check(str_contains($a10body, '#33') || str_contains($a10body, 'Servo'), '#10 teaser #33 Servo');

echo "\n--- C: Tidak ada orphan 'Artikel #32' di seeder backlink ---\n\n";

foreach ([
    'Article1Seeder.php', 'Article10Seeder.php', 'Article31Seeder.php',
] as $file) {
    $path = __DIR__ . '/../database/seeders/' . $file;
    if (! file_exists($path)) {
        continue;
    }
    $src = file_get_contents($path);
    check(! preg_match('/Artikel #32/', $src), "{$file}: tidak ada teks orphan 'Artikel #32'");
}

echo "\n--- D: Outbound #32 tidak ada link mati / self-link ---\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    if ($target === '') {
        continue;
    }
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

echo "\n--- E: Konten teknis edge-case ---\n\n";

check(str_contains($body, 'BLEDevice'), 'API BLEDevice');
check(str_contains($body, 'BLE2902'), 'Descriptor BLE2902');
check(str_contains($body, 'KindoESP32-DHT22'), 'Nama perangkat');
check(str_contains($body, 'b10d4001-0001-4001-8001-000032000001'), 'Service UUID');
check(str_contains($body, 'nRF Connect'), 'nRF Connect');
check(str_contains($body, '1782977400'), 'Unix timestamp konsisten #34');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic MQTT referensi');
check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(str_contains($body, 'Servo') && str_contains($body, '#33'), 'Teaser Servo #33');
check(str_contains($body, 'Checklist'), 'Section checklist');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');

echo "\n--- F: Render HTML kritis ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'Bluetooth') && str_contains($html, 'BLE2902'), 'Konten kunci ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 18, 'H2 ter-render ≥18 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #31 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle32\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m32);
$h32 = $m32[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h32, $needle), "Hook #32 punya {$needle}");
}

check(strpos($h32, 'Article32Seeder') !== false && strpos($h32, 'Article31Seeder') !== false && strpos($h32, 'Article32Seeder') < strpos($h32, 'Article31Seeder'), 'Urutan: seed #32 → re-seed #31');
check(str_contains($h32, 'PatchArticle1BluetoothSeeder'), 'Hook #32 PatchArticle1BluetoothSeeder');
check(strpos($deploy, 'function publishArticle31') < strpos($deploy, 'function publishArticle32'), 'DeployController: #31 hook sebelum #32 hook');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 32 via deploy hook \(required\)/', $yml) === 1, 'CI hook #32 required');

echo "\n--- H: Regresi audit batch ---\n\n";

foreach ([
    'audit-article32.php', 'audit-article31.php', 'audit-article10.php',
    'audit-article32-manual.php', 'audit-article32-spotcheck.php',
] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

echo "\n--- I: Production state (pre-deploy #32) ---\n\n";

$prod32 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod32 === '404';
check($preDeploy, "Production #32 belum live (HTTP {$prod32}) — expected pre-deploy", ! $preDeploy);

$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
$prod31live = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug31))) === '200';

if ($preDeploy) {
    $prod31html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug31));
    check(! str_contains($prod31html, $slug), 'Production #31 belum punya hyperlink #32 (normal pre-deploy)', str_contains($prod31html, $slug));
    if ($prod31live) {
        check(str_contains($prod10html, 'dua puluh dua artikel'), 'Production #10 dua puluh dua artikel (pasca-deploy #31, pre-#32)');
        check(str_contains($prod10html, $slug31), 'Production #10 link #31 di indeks');
        check(! str_contains($prod10html, $slug), 'Production #10 belum punya link #32 (normal pre-deploy)');
    }
} else {
    check(str_contains($prod10html, 'dua puluh tiga artikel'), 'Production #10 dua puluh tiga artikel (pasca-deploy #32)');
    check(str_contains($prod10html, $slug), 'Production #10 link #32 di indeks');
}

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) || str_contains($todo, '#32'), 'TODO.md punya #32');
    check(str_contains($todo, 'siap deploy') || str_contains($todo, '[~]'), 'TODO.md status drafting/siap deploy');
    check(str_contains($prd, '#32') || str_contains($prd, 'BLE'), 'PRD.md menyebut #32');
    check(str_contains($roadmap, 'bluetooth-esp32') || str_contains($roadmap, 'BLE'), 'roadmap punya #32');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
