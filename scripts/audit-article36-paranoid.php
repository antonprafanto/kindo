<?php

/**
 * Paranoid audit #36.
 * Usage: php scripts/audit-article36-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article36Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0; $failed = 0; $warn = 0;
$slug = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';
$slug35 = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';

function check(bool $ok, string $label, bool $warning = false): void {
    global $passed, $failed, $warn;
    if ($warning && ! $ok) { echo "⚠ {$label}\n"; $warn++; return; }
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article36Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #36 ===\n\n";

foreach (['Article36Seeder', 'Article10Seeder', 'PatchArticle1Esp8266Seeder', 'PatchArticle35Esp8266Seeder', 'RemoveDuplicateBme280Seeder'] as $cls) {
    check(Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]) === 0, "Seed {$cls} exit 0");
}

$a36 = Article::where('slug', $slug)->first();
check($a36 && $a36->status === 'published', 'Artikel #36 visible setelah full chain');
check($a36?->tags()->count() === 6, 'Tag count = 6');
check($a36?->read_time_minutes >= 8, 'read_time ≥ 8 menit');

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 26, '#10 indeks 26 item (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh enam artikel'), '#10 teks dua puluh enam artikel');
check(str_contains($a10body, $slug), '#10 item #36');
check(str_contains($a10body, '#37') || str_contains($a10body, 'SD Card'), '#10 teaser #37');

foreach (['Article10Seeder.php', 'Article35Seeder.php'] as $file) {
    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! preg_match('/Artikel #36/', $src), "{$file}: tidak ada orphan 'Artikel #36'");
}

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    if ($target === '') continue;
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

check(str_contains($body, 'ESP8266WiFi.h'), 'Include ESP8266WiFi.h');
check(str_contains($body, 'kodingindonesia/esp8266/dht22/data'), 'Topic esp8266');
check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'ESP8266') || str_contains($html, 'NodeMCU'), 'Konten kunci ter-render');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle36\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m36);
$h36 = $m36[1] ?? '';
foreach (['opcache_reset', 'SitemapService', 'view:clear', 'runDuplicateBme280Cleanup', 'PatchArticle1Esp8266Seeder', 'PatchArticle35Esp8266Seeder'] as $needle) {
    check(str_contains($h36, $needle), "Hook #36 punya {$needle}");
}
check(strpos($deploy, 'function publishArticle35') < strpos($deploy, 'function publishArticle36'), 'DeployController: #35 sebelum #36');
check(preg_match('/Publish article 36 via deploy hook \(required\)/', file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml')) === 1, 'CI hook #36 required');

foreach (['audit-article36.php', 'audit-article35.php', 'audit-article10.php', 'audit-article36-manual.php', 'audit-article36-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

$prod36 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod36 === '404';
check($preDeploy, "Production #36 belum live (HTTP {$prod36}) — expected pre-deploy", ! $preDeploy);

$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
if ($preDeploy) {
    check(str_contains($prod10html, 'dua puluh lima artikel'), 'Production #10 dua puluh lima artikel (pre-#36)');
    check(! str_contains($prod10html, $slug), 'Production #10 belum link #36');
    $prod35html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug35));
    check(! str_contains($prod35html, $slug), 'Production #35 belum hyperlink #36 (normal pre-deploy)');
}

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    check(str_contains($todo, '#36') || str_contains($todo, $slug), 'TODO.md punya #36');
    check(str_contains(file_get_contents($docsRoot . '/PRD.md'), '#36') || str_contains(file_get_contents($docsRoot . '/PRD.md'), 'ESP8266'), 'PRD.md #36');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
