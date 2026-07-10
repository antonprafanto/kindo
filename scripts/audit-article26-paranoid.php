<?php

/**
 * Paranoid audit #26 — lapisan ketiga di luar ultra/extra/final.
 * Usage: php scripts/audit-article26-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article26Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';
$href = '/artikel/' . $slug;
$slug25 = 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi';
$href25 = '/artikel/' . $slug25;

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

$ref = new ReflectionClass(Article26Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #26 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

$hookSeeders = [
    'Article26Seeder', 'Article25Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article11Seeder', 'Article7Seeder', 'RemoveDuplicateBme280Seeder',
];

foreach ($hookSeeders as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a26 = Article::where('slug', $slug)->first();
check($a26 && $a26->status === 'published', 'Artikel #26 visible setelah full chain');
check($a26?->tags()->count() === 6, 'Tag count = 6 (' . ($a26?->tags()->count() ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 17, '#10 indeks punya 17 item live (' . $indexItems . ')');
check(str_contains($a10body, 'tujuh belas artikel'), '#10 teks tujuh belas artikel');
check(str_contains($a10body, $href), '#10 item #26 di indeks');
check(str_contains($a10body, '#27') || str_contains($a10body, 'ESP32-CAM'), '#10 teaser #27 ESP32-CAM di Masih akan datang');

echo "\n--- C: Tidak ada orphan 'Artikel #26' di seeder backlink ---\n\n";

foreach ([
    'Article7Seeder.php', 'Article10Seeder.php', 'Article11Seeder.php',
    'Article20Seeder.php', 'Article25Seeder.php',
] as $file) {
    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! preg_match('/Artikel #26/', $src), "{$file}: tidak ada teks orphan 'Artikel #26'");
}

echo "\n--- D: Outbound #26 tidak ada link mati / self-link ---\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

echo "\n--- E: Konten teknis edge-case ---\n\n";

check(str_contains($body, 'LoRa.begin'), 'API LoRa.begin');
check(str_contains($body, 'setSyncWord'), 'Sync word pairing');
check(str_contains($body, 'DHT22'), 'Sensor DHT22');
check(str_contains($body, 'deep sleep'), 'Referensi deep sleep #11');
check(str_contains($body, 'sensor') && str_contains($body, 'receiver'), 'Pola sensor + receiver');
check(str_contains($body, 'Checklist'), 'Checklist kapan pakai');

echo "\n--- F: Render HTML kritis ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'SX1278') && str_contains($html, 'mosquitto_sub'), 'Konten kunci ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 18, 'H2 ter-render ≥18 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #25 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle26\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m26);
preg_match('/function publishArticle25\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m25);
$h26 = $m26[1] ?? '';
$h25 = $m25[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h26, $needle), "Hook #26 punya {$needle}");
}

check(strpos($h26, 'Article26Seeder') !== false && strpos($h26, 'Article25Seeder') !== false && strpos($h26, 'Article26Seeder') < strpos($h26, 'Article25Seeder'), 'Urutan: seed #26 → re-seed #25');
check(strpos($deploy, 'function publishArticle25') < strpos($deploy, 'function publishArticle26'), 'DeployController: #25 hook sebelum #26 hook');

echo "\n--- H: Regresi audit batch ---\n\n";

foreach ([
    'audit-article25.php', 'audit-article10.php', 'audit-article20.php', 'audit-article25-manual.php',
] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

echo "\n--- I: Production state (pre-deploy #26) ---\n\n";

$prod26 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod26 === '404';
check($preDeploy, "Production #26 belum live (HTTP {$prod26}) — expected pre-deploy", ! $preDeploy);

if ($preDeploy) {
    $prod25html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug25));
    $prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
    check(! str_contains($prod25html, $href), 'Production #25 belum punya hyperlink #26 (normal pre-deploy)');
    check(str_contains($prod10html, 'enam belas artikel'), 'Production #10 masih enam belas — akan fix saat deploy hook #26');
} else {
    $prod25html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug25));
    $prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
    check(str_contains($prod25html, $href), 'Production #25 hyperlink #26');
    check(str_contains($prod10html, 'tujuh belas artikel'), 'Production #10 tujuh belas artikel');
    check(str_contains($prod10html, $href), 'Production #10 link #26 di indeks');
}

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'), 'TODO.md punya slug #26');
    check(str_contains($prd, '#26') || str_contains($prd, 'LoRa'), 'PRD.md menyebut #26');
    check(str_contains($roadmap, 'lora') || str_contains($roadmap, 'LoRa'), 'roadmap punya #26');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
