<?php

/**
 * Paranoid audit #25 — lapisan ketiga di luar ultra/extra/final.
 * Usage: php scripts/audit-article25-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article25Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi';
$href = '/artikel/' . $slug;
$slug20 = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
$href20 = '/artikel/' . $slug20;

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

$ref = new ReflectionClass(Article25Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #25 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

$hookSeeders = [
    'Article25Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article11Seeder', 'Article7Seeder', 'RemoveDuplicateBme280Seeder',
];

foreach ($hookSeeders as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a25 = Article::where('slug', $slug)->first();
check($a25 && $a25->status === 'published', 'Artikel #25 visible setelah full chain');
check($a25?->tags()->count() === 6, 'Tag count = 6 (' . ($a25?->tags()->count() ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 22, '#10 indeks punya 22 item live (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh dua artikel'), '#10 teks dua puluh dua artikel');
check(str_contains($a10body, $href), '#10 item #25 di indeks');
check(str_contains($a10body, 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'), '#10 item #26 di indeks');
check(str_contains($a10body, 'esp32-cam-streaming-mjpeg-capture-foto-wifi'), '#10 item #27 di indeks');
check(str_contains($a10body, 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard'), '#10 item #28 di indeks');
check(str_contains($a10body, 'migrasi-platformio-esp32-vscode-project-rapi'), '#10 item #29 di indeks');
check(str_contains($a10body, '#30') || str_contains($a10body, 'Firebase'), '#10 teaser #30 Firebase');

echo "\n--- C: Tidak ada orphan 'Artikel #25' di seeder backlink ---\n\n";

foreach ([
    'Article7Seeder.php', 'Article10Seeder.php', 'Article11Seeder.php', 'Article20Seeder.php',
] as $file) {
    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! preg_match('/Artikel #25/', $src), "{$file}: tidak ada teks orphan 'Artikel #25'");
}

echo "\n--- D: Outbound #25 tidak ada link mati / self-link ---\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

echo "\n--- E: Konten teknis edge-case ---\n\n";

check(str_contains($body, 'esp_now_init'), 'API esp_now_init');
check(str_contains($body, 'esp_now_add_peer'), 'Peer add API');
check(str_contains($body, 'PubSubClient'), 'MQTT gateway sketch');
check(str_contains($body, 'deep sleep'), 'Referensi deep sleep #11');
check(str_contains($body, 'sensor + gateway'), 'Pola sensor + gateway');
check(str_contains($body, 'Checklist'), 'Checklist kapan pakai');

echo "\n--- F: Render HTML kritis ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'ESP-NOW') && str_contains($html, 'mosquitto_sub'), 'Konten kunci ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 19, 'H2 ter-render ≥19 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #20 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle25\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m25);
preg_match('/function publishArticle20\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m20);
$h25 = $m25[1] ?? '';
$h20 = $m20[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h25, $needle), "Hook #25 punya {$needle}");
}

check(strpos($h25, 'Article25Seeder') !== false && strpos($h25, 'Article20Seeder') !== false && strpos($h25, 'Article25Seeder') < strpos($h25, 'Article20Seeder'), 'Urutan: seed #25 → re-seed #20');
check(strpos($deploy, 'function publishArticle20') < strpos($deploy, 'function publishArticle25'), 'DeployController: #20 hook sebelum #25 hook');

echo "\n--- H: Regresi audit batch ---\n\n";

foreach ([
    'audit-article20.php', 'audit-article10.php', 'audit-article11.php', 'audit-article20-manual.php',
] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

echo "\n--- I: Production state (pre-deploy #25) ---\n\n";

$prod25 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod25 === '404';
check($preDeploy, "Production #25 belum live (HTTP {$prod25}) — expected pre-deploy", ! $preDeploy);

if ($preDeploy) {
    $prod20html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug20));
    $prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
    check(! str_contains($prod20html, $href), 'Production #20 belum punya hyperlink #25 (normal pre-deploy)');
    check(str_contains($prod10html, 'enam belas artikel'), 'Production #10 masih enam belas — akan fix saat deploy hook #26');
} else {
    $prod20html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug20));
    $prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
    $slug28 = 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard';
    $slug29 = 'migrasi-platformio-esp32-vscode-project-rapi';
    $prod28live = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug28))) === '200';
    $prod29live = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug29))) === '200';
    check(str_contains($prod20html, $href), 'Production #20 hyperlink #25');
    check(str_contains($prod10html, $href), 'Production #10 link #25 di indeks');
    if ($prod29live) {
        check(str_contains($prod10html, 'dua puluh artikel'), 'Production #10 dua puluh artikel (pasca-deploy #29)');
        check(str_contains($prod10html, $slug29), 'Production #10 link #29 di indeks');
    } elseif ($prod28live) {
        check(str_contains($prod10html, 'sembilan belas artikel'), 'Production #10 sembilan belas artikel (pasca-deploy #28, pre-#29)');
        check(str_contains($prod10html, $slug28), 'Production #10 link #28 di indeks');
        check(! str_contains($prod10html, $slug29), 'Production #10 belum punya link #29 (normal pre-deploy)');
    } else {
        check(
            str_contains($prod10html, 'tujuh belas artikel')
            || str_contains($prod10html, 'delapan belas artikel')
            || str_contains($prod10html, 'enam belas artikel'),
            'Production #10 indeks artikel (pre-deploy #28)'
        );
    }
}

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'), 'TODO.md punya slug #25');
    check(str_contains($prd, '#25') || str_contains($prd, 'ESP-NOW'), 'PRD.md menyebut #25');
    check(str_contains($roadmap, 'esp-now'), 'roadmap punya #25');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
