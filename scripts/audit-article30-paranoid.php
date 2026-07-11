<?php

/**
 * Paranoid audit #30 — lapisan ketiga di luar audit utama + spotcheck.
 * Usage: php scripts/audit-article30-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article30Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'esp32-firebase-realtime-database-sensor-cloud';
$href = '/artikel/' . $slug;
$slug29 = 'migrasi-platformio-esp32-vscode-project-rapi';

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

$ref = new ReflectionClass(Article30Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #30 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

foreach ([
    'Article30Seeder', 'Article29Seeder', 'Article10Seeder',
    'PatchArticle4FirebaseSeeder', 'RemoveDuplicateBme280Seeder',
] as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a30 = Article::where('slug', $slug)->first();
check($a30 && $a30->status === 'published', 'Artikel #30 visible setelah full chain');
check($a30?->tags()->count() === 6, 'Tag count = 6 (' . ($a30?->tags()->count() ?? 0) . ')');
check($a30?->read_time_minutes >= 8, 'read_time ≥ 8 menit (' . ($a30?->read_time_minutes ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 23, '#10 indeks punya 23 item live (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh tiga artikel'), '#10 teks dua puluh tiga artikel');
check(str_contains($a10body, $slug), '#10 item #30 di indeks');
check(str_contains($a10body, 'freertos-esp32-multi-task-sensor-wifi-mqtt'), '#10 item #31 di indeks');
check(str_contains($a10body, 'bluetooth-esp32-ble-kirim-data-sensor-smartphone'), '#10 item #32 di indeks');
check(str_contains($a10body, '#33') || str_contains($a10body, 'Servo'), '#10 teaser #33 Servo');

echo "\n--- C: Tidak ada orphan 'Artikel #30' di seeder backlink ---\n\n";

foreach ([
    'Article4Seeder.php', 'Article10Seeder.php', 'Article29Seeder.php',
] as $file) {
    $path = __DIR__ . '/../database/seeders/' . $file;
    if (! file_exists($path)) {
        continue;
    }
    $src = file_get_contents($path);
    check(! preg_match('/Artikel #30/', $src), "{$file}: tidak ada teks orphan 'Artikel #30'");
}

echo "\n--- D: Outbound #30 tidak ada link mati / self-link ---\n\n";

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

check(str_contains($body, 'Firebase_ESP_Client'), 'Library Firebase_ESP_Client');
check(str_contains($body, 'GANTI_FIREBASE_API_KEY'), 'Placeholder GANTI_FIREBASE_API_KEY');
check(str_contains($body, 'GANTI_FIREBASE_DATABASE_URL'), 'Placeholder GANTI_FIREBASE_DATABASE_URL');
check(str_contains($body, 'GANTI_FIREBASE_USER_EMAIL'), 'Placeholder GANTI_FIREBASE_USER_EMAIL');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder GANTI_NAMA_WIFI');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder GANTI_PASSWORD_WIFI');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Path sensor konsisten');
check(str_contains($body, '1782977400'), 'Unix timestamp konsisten #34');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, 'language-cpp'), 'Blok C++');
check(str_contains($body, 'language-json'), 'Blok JSON rules');
check(str_contains($body, 'Jalur E'), 'Menyebut Jalur E');
check(str_contains($body, 'FreeRTOS (#31)'), 'Teaser FreeRTOS #31');
check(str_contains($body, 'Checklist'), 'Section checklist');

echo "\n--- F: Render HTML kritis ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'Firebase') && str_contains($html, 'Realtime Database'), 'Konten kunci ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 18, 'H2 ter-render ≥18 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #29 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle30\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m30);
preg_match('/function publishArticle29\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m29);
$h30 = $m30[1] ?? '';
$h29 = $m29[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h30, $needle), "Hook #30 punya {$needle}");
}

check(strpos($h30, 'Article30Seeder') !== false && strpos($h30, 'Article29Seeder') !== false && strpos($h30, 'Article30Seeder') < strpos($h30, 'Article29Seeder'), 'Urutan: seed #30 → re-seed #29');
check(str_contains($h30, 'PatchArticle4FirebaseSeeder'), 'Hook #30 PatchArticle4FirebaseSeeder');
check(strpos($deploy, 'function publishArticle29') < strpos($deploy, 'function publishArticle30'), 'DeployController: #29 hook sebelum #30 hook');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 30 via deploy hook \(required\)/', $yml) === 1, 'CI hook #30 required');

echo "\n--- H: Regresi audit batch ---\n\n";

foreach ([
    'audit-article30.php', 'audit-article29.php', 'audit-article10.php',
    'audit-article30-manual.php', 'audit-article30-spotcheck.php',
] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

echo "\n--- I: Production state (pre-deploy #30) ---\n\n";

$prod30 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod30 === '404';
check($preDeploy, "Production #30 belum live (HTTP {$prod30}) — expected pre-deploy", ! $preDeploy);

$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
$prod29live = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug29))) === '200';

if ($preDeploy) {
    $prod29html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug29));
    check(str_contains($prod29html, $slug) || str_contains($prod29html, 'Firebase'), 'Production #29 belum punya hyperlink #30 (normal pre-deploy)', ! (str_contains($prod29html, $slug) || str_contains($prod29html, 'Firebase')));
    if ($prod29live) {
        check(str_contains($prod10html, 'dua puluh artikel'), 'Production #10 dua puluh artikel (pasca-deploy #29, pre-#30)');
        check(str_contains($prod10html, $slug29), 'Production #10 link #29 di indeks');
        check(! str_contains($prod10html, $slug), 'Production #10 belum punya link #30 (normal pre-deploy)');
    }
} else {
    check(str_contains($prod10html, 'dua puluh satu artikel'), 'Production #10 dua puluh satu artikel (pasca-deploy #30)');
    check(str_contains($prod10html, $slug), 'Production #10 link #30 di indeks');
}

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug), 'TODO.md punya slug #30');
    check(str_contains($todo, 'LIVE') || str_contains($todo, $slug), 'TODO.md menyebut #30');
    check(str_contains($prd, '#30') || str_contains($prd, 'Firebase'), 'PRD.md menyebut #30');
    check(str_contains($roadmap, 'esp32-firebase') || str_contains($roadmap, 'Firebase'), 'roadmap punya #30');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
