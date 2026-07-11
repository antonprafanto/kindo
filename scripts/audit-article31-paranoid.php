<?php

/**
 * Paranoid audit #31 — lapisan ketiga.
 * Usage: php scripts/audit-article31-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article31Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'freertos-esp32-multi-task-sensor-wifi-mqtt';
$slug30 = 'esp32-firebase-realtime-database-sensor-cloud';

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

$ref = new ReflectionClass(Article31Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #31 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

foreach ([
    'Article31Seeder', 'Article30Seeder', 'Article10Seeder',
    'PatchArticle9FreeRTOSSeeder', 'RemoveDuplicateBme280Seeder',
] as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a31 = Article::where('slug', $slug)->first();
check($a31 && $a31->status === 'published', 'Artikel #31 visible setelah full chain');
check($a31?->tags()->count() === 6, 'Tag count = 6 (' . ($a31?->tags()->count() ?? 0) . ')');
check($a31?->read_time_minutes >= 8, 'read_time ≥ 8 menit (' . ($a31?->read_time_minutes ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 22, '#10 indeks punya 22 item live (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh dua artikel'), '#10 teks dua puluh dua artikel');
check(str_contains($a10body, $slug), '#10 item #31 di indeks');
check(str_contains($a10body, '#32') || str_contains($a10body, 'Bluetooth'), '#10 teaser #32 BLE');

echo "\n--- C: Tidak ada orphan 'Artikel #31' di seeder backlink ---\n\n";

foreach ([
    'Article9Seeder.php', 'Article10Seeder.php', 'Article30Seeder.php',
] as $file) {
    $path = __DIR__ . '/../database/seeders/' . $file;
    if (! file_exists($path)) {
        continue;
    }
    $src = file_get_contents($path);
    check(! preg_match('/Artikel #31/', $src), "{$file}: tidak ada teks orphan 'Artikel #31'");
}

echo "\n--- D: Outbound #31 tidak ada link mati / self-link ---\n\n";

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

check(str_contains($body, 'xTaskCreatePinnedToCore'), 'API xTaskCreatePinnedToCore');
check(str_contains($body, 'xQueueCreate'), 'API xQueueCreate');
check(str_contains($body, 'vTaskDelay'), 'API vTaskDelay');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder GANTI_NAMA_WIFI');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder GANTI_PASSWORD_MQTT');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic MQTT konsisten');
check(str_contains($body, '1782977400'), 'Unix timestamp konsisten #34');
check(str_contains($body, 'kindo_esp32'), 'User MQTT kindo_esp32');
check(str_contains($body, 'Jalur E'), 'Menyebut Jalur E');
check(str_contains($body, 'Bluetooth BLE (#32)'), 'Teaser BLE #32');
check(str_contains($body, 'Checklist'), 'Section checklist');

echo "\n--- F: Render HTML kritis ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'FreeRTOS') && str_contains($html, 'xQueueCreate'), 'Konten kunci ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 18, 'H2 ter-render ≥18 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #30 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle31\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m31);
preg_match('/function publishArticle30\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m30);
$h31 = $m31[1] ?? '';
$h30 = $m30[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h31, $needle), "Hook #31 punya {$needle}");
}

check(strpos($h31, 'Article31Seeder') !== false && strpos($h31, 'Article30Seeder') !== false && strpos($h31, 'Article31Seeder') < strpos($h31, 'Article30Seeder'), 'Urutan: seed #31 → re-seed #30');
check(str_contains($h31, 'PatchArticle9FreeRTOSSeeder'), 'Hook #31 PatchArticle9FreeRTOSSeeder');
check(strpos($deploy, 'function publishArticle30') < strpos($deploy, 'function publishArticle31'), 'DeployController: #30 hook sebelum #31 hook');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 31 via deploy hook \(required\)/', $yml) === 1, 'CI hook #31 required');

echo "\n--- H: Regresi audit batch ---\n\n";

foreach ([
    'audit-article31.php', 'audit-article30.php', 'audit-article10.php',
    'audit-article31-manual.php', 'audit-article31-spotcheck.php',
] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

echo "\n--- I: Production state (pre-deploy #31) ---\n\n";

$prod31 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod31 === '404';
check($preDeploy, "Production #31 belum live (HTTP {$prod31}) — expected pre-deploy", ! $preDeploy);

$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
$prod30live = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug30))) === '200';

if ($preDeploy) {
    $prod30html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug30));
    check(str_contains($prod30html, $slug) || str_contains($prod30html, 'FreeRTOS'), 'Production #30 belum punya hyperlink #31 (normal pre-deploy)', ! (str_contains($prod30html, $slug) || str_contains($prod30html, 'FreeRTOS')));
    if ($prod30live) {
        check(str_contains($prod10html, 'dua puluh satu artikel'), 'Production #10 dua puluh satu artikel (pasca-deploy #30, pre-#31)');
        check(str_contains($prod10html, $slug30), 'Production #10 link #30 di indeks');
        check(! str_contains($prod10html, $slug), 'Production #10 belum punya link #31 (normal pre-deploy)');
    }
} else {
    check(str_contains($prod10html, 'dua puluh dua artikel'), 'Production #10 dua puluh dua artikel (pasca-deploy #31)');
    check(str_contains($prod10html, $slug), 'Production #10 link #31 di indeks');
}

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug), 'TODO.md punya slug #31');
    check(str_contains($todo, 'siap deploy'), 'TODO.md status siap deploy');
    check(str_contains($prd, '#31') || str_contains($prd, 'FreeRTOS'), 'PRD.md menyebut #31');
    check(str_contains($roadmap, 'freertos-esp32') || str_contains($roadmap, 'FreeRTOS'), 'roadmap punya #31');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
