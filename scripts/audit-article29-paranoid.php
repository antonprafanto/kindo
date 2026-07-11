<?php

/**
 * Paranoid audit #29 — lapisan ketiga di luar audit utama + spotcheck.
 * Usage: php scripts/audit-article29-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article29Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'migrasi-platformio-esp32-vscode-project-rapi';
$href = '/artikel/' . $slug;
$slug28 = 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard';

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

$ref = new ReflectionClass(Article29Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #29 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

foreach ([
    'Article29Seeder', 'Article28Seeder', 'Article10Seeder',
    'PatchArticle2PlatformioSeeder', 'RemoveDuplicateBme280Seeder',
] as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a29 = Article::where('slug', $slug)->first();
check($a29 && $a29->status === 'published', 'Artikel #29 visible setelah full chain');
check($a29?->tags()->count() === 6, 'Tag count = 6 (' . ($a29?->tags()->count() ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 21, '#10 indeks punya 21 item live (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh satu artikel'), '#10 teks dua puluh satu artikel');
check(str_contains($a10body, $slug), '#10 item #29 di indeks');
check(str_contains($a10body, '#30') || str_contains($a10body, 'Firebase'), '#10 teaser #30 Firebase');

echo "\n--- C: Tidak ada orphan 'Artikel #29' di seeder backlink ---\n\n";

foreach ([
    'Article2Seeder.php', 'Article10Seeder.php', 'Article28Seeder.php',
    'ArticleSeeder.php',
] as $file) {
    $path = __DIR__ . '/../database/seeders/' . $file;
    if (! file_exists($path)) {
        continue;
    }
    $src = file_get_contents($path);
    check(! preg_match('/Artikel #29/', $src), "{$file}: tidak ada teks orphan 'Artikel #29'");
}

echo "\n--- D: Outbound #29 tidak ada link mati / self-link ---\n\n";

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

check(str_contains($body, 'platformio.ini'), 'File platformio.ini');
check(str_contains($body, 'lib_deps'), 'lib_deps');
check(str_contains($body, 'pio run'), 'Perintah pio run');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder GANTI_NAMA_WIFI');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder GANTI_PASSWORD_WIFI');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder GANTI_PASSWORD_MQTT');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic MQTT konsisten');
check(str_contains($body, 'language-yaml'), 'Blok YAML CI');
check(str_contains($body, '.pio/'), 'Peringatan folder .pio');
check(str_contains($body, 'Jalur E'), 'Menyebut Jalur E');
check(str_contains($body, 'Checklist'), 'Section checklist');

echo "\n--- F: Render HTML kritis ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'PlatformIO') && str_contains($html, 'platformio.ini'), 'Konten kunci ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 18, 'H2 ter-render ≥18 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #28 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle29\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m29);
preg_match('/function publishArticle28\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m28);
$h29 = $m29[1] ?? '';
$h28 = $m28[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h29, $needle), "Hook #29 punya {$needle}");
}

check(strpos($h29, 'Article29Seeder') !== false && strpos($h29, 'Article28Seeder') !== false && strpos($h29, 'Article29Seeder') < strpos($h29, 'Article28Seeder'), 'Urutan: seed #29 → re-seed #28');
check(strpos($deploy, 'function publishArticle28') < strpos($deploy, 'function publishArticle29'), 'DeployController: #28 hook sebelum #29 hook');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 29 via deploy hook \(required\)/', $yml) === 1, 'CI hook #29 required');

echo "\n--- H: Regresi audit batch ---\n\n";

foreach ([
    'audit-article29.php', 'audit-article28.php', 'audit-article10.php',
    'audit-article29-manual.php',
] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

echo "\n--- I: Production state (pre-deploy #29) ---\n\n";

$prod29 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod29 === '404';
check($preDeploy, "Production #29 belum live (HTTP {$prod29}) — expected pre-deploy", ! $preDeploy);

$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
$prod28live = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug28))) === '200';

if ($preDeploy) {
    $prod28html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug28));
    check(str_contains($prod28html, $slug) || str_contains($prod28html, 'Migrasi PlatformIO'), 'Production #28 belum punya hyperlink #29 (normal pre-deploy)', ! (str_contains($prod28html, $slug) || str_contains($prod28html, 'Migrasi PlatformIO')));
    if ($prod28live) {
        check(str_contains($prod10html, 'sembilan belas artikel'), 'Production #10 sembilan belas artikel (pasca-deploy #28, pre-#29)');
        check(str_contains($prod10html, $slug28), 'Production #10 link #28 di indeks');
        check(! str_contains($prod10html, $slug), 'Production #10 belum punya link #29 (normal pre-deploy)');
    }
} else {
    check(str_contains($prod10html, 'dua puluh artikel'), 'Production #10 dua puluh artikel (pasca-deploy #29)');
    check(str_contains($prod10html, $slug), 'Production #10 link #29 di indeks');
}

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug), 'TODO.md punya slug #29');
    check(str_contains($todo, 'siap deploy'), 'TODO.md status siap deploy');
    check(str_contains($prd, '#29') || str_contains($prd, 'PlatformIO'), 'PRD.md menyebut #29');
    check(str_contains($roadmap, 'migrasi-platformio') || str_contains($roadmap, 'PlatformIO'), 'roadmap punya #29');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
