<?php

/**
 * Paranoid audit #27 — lapisan ketiga di luar ultra/extra/final.
 * Usage: php scripts/audit-article27-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article27Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';
$href = '/artikel/' . $slug;
$slug26 = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';
$href26 = '/artikel/' . $slug26;

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

$ref = new ReflectionClass(Article27Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #27 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

$hookSeeders = [
    'Article27Seeder', 'Article26Seeder', 'Article10Seeder', 'Article6Seeder',
    'Article20Seeder', 'RemoveDuplicateBme280Seeder',
];

foreach ($hookSeeders as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a27 = Article::where('slug', $slug)->first();
check($a27 && $a27->status === 'published', 'Artikel #27 visible setelah full chain');
check($a27?->tags()->count() === 6, 'Tag count = 6 (' . ($a27?->tags()->count() ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 19, '#10 indeks punya 19 item live (' . $indexItems . ')');
check(str_contains($a10body, 'sembilan belas artikel'), '#10 teks sembilan belas artikel');
check(str_contains($a10body, $href), '#10 item #27 di indeks');
check(str_contains($a10body, 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard'), '#10 item #28 di indeks');
check(str_contains($a10body, '#29') || str_contains($a10body, 'PlatformIO'), '#10 teaser #29 PlatformIO');

echo "\n--- C: Tidak ada orphan 'Artikel #27' di seeder backlink ---\n\n";

foreach ([
    'Article6Seeder.php', 'Article10Seeder.php', 'Article20Seeder.php',
    'Article26Seeder.php',
] as $file) {
    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! preg_match('/Artikel #27/', $src), "{$file}: tidak ada teks orphan 'Artikel #27'");
}

echo "\n--- D: Outbound #27 tidak ada link mati / self-link ---\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

echo "\n--- E: Konten teknis edge-case ---\n\n";

check(str_contains($body, 'esp_camera_init'), 'API esp_camera_init');
check(str_contains($body, 'esp_camera_fb_get'), 'API esp_camera_fb_get');
check(str_contains($body, 'MJPEG'), 'Format MJPEG');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder GANTI_NAMA_WIFI');
check(str_contains($body, '/stream') && str_contains($body, '/capture'), 'Endpoint /stream + /capture');
check(str_contains($body, 'ESP32-CAM'), 'Modul ESP32-CAM');
check(str_contains($body, 'Checklist'), 'Checklist kapan pakai');

echo "\n--- F: Render HTML kritis ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'ESP32-CAM') && str_contains($html, 'esp_camera_init'), 'Konten kunci ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 18, 'H2 ter-render ≥18 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #26 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle27\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m27);
preg_match('/function publishArticle26\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m26);
$h27 = $m27[1] ?? '';
$h26 = $m26[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h27, $needle), "Hook #27 punya {$needle}");
}

check(strpos($h27, 'Article27Seeder') !== false && strpos($h27, 'Article26Seeder') !== false && strpos($h27, 'Article27Seeder') < strpos($h27, 'Article26Seeder'), 'Urutan: seed #27 → re-seed #26');
check(strpos($deploy, 'function publishArticle26') < strpos($deploy, 'function publishArticle27'), 'DeployController: #26 hook sebelum #27 hook');

echo "\n--- H: Regresi audit batch ---\n\n";

foreach ([
    'audit-article26.php', 'audit-article10.php', 'audit-article20.php', 'audit-article6.php',
] as $script) {
    $path = __DIR__ . '/' . $script;
    if (! file_exists($path)) {
        check(false, "{$script} tidak ada");
        continue;
    }
    exec('php ' . escapeshellarg($path) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

echo "\n--- I: Production state (pre-deploy #27) ---\n\n";

$prod27 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod27 === '404';
check($preDeploy, "Production #27 belum live (HTTP {$prod27}) — expected pre-deploy", ! $preDeploy);

if ($preDeploy) {
    $prod26code = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug26)));
    check($prod26code === '200', "Production #26 sudah live HTTP {$prod26code}");

    $prod26html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug26));
    $prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
    check(! str_contains($prod26html, $href), 'Production #26 belum punya hyperlink #27 (normal pre-deploy)');
    check(str_contains($prod10html, 'tujuh belas artikel'), 'Production #10 masih tujuh belas — akan fix saat deploy hook #27');
} else {
    $slug28 = 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard';
    $prod28live = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug28))) === '200';
    $prod26html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug26));
    $prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
    check(str_contains($prod26html, $href), 'Production #26 hyperlink #27');
    if ($prod28live) {
        check(str_contains($prod10html, 'sembilan belas artikel'), 'Production #10 sembilan belas artikel (pasca-deploy #28)');
        check(str_contains($prod10html, $slug28), 'Production #10 link #28 di indeks');
    } else {
        check(str_contains($prod10html, 'delapan belas artikel'), 'Production #10 delapan belas artikel (pre-deploy #28)');
        check(str_contains($prod10html, $href), 'Production #10 link #27 di indeks');
        check(! str_contains($prod10html, $slug28), 'Production #10 belum punya link #28 (normal pre-deploy)');
    }
}

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, 'esp32-cam-streaming-mjpeg-capture-foto-wifi'), 'TODO.md punya slug #27');
    check(str_contains($prd, '#27') || str_contains($prd, 'ESP32-CAM'), 'PRD.md menyebut #27');
    check(str_contains($roadmap, 'esp32-cam') || str_contains($roadmap, 'ESP32-CAM'), 'roadmap punya #27');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
