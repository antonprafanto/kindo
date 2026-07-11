<?php

/**
 * Paranoid audit #35.
 * Usage: php scripts/audit-article35-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article35Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0; $failed = 0; $warn = 0;
$slug = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';
$slug33 = 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi';

function check(bool $ok, string $label, bool $warning = false): void {
    global $passed, $failed, $warn;
    if ($warning && ! $ok) { echo "⚠ {$label}\n"; $warn++; return; }
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article35Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #35 ===\n\n";

foreach (['Article35Seeder', 'Article33Seeder', 'Article10Seeder', 'PatchArticle5AdcSeeder', 'PatchArticle27LdrSeeder', 'RemoveDuplicateBme280Seeder'] as $cls) {
    check(Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]) === 0, "Seed {$cls} exit 0");
}

$a35 = Article::where('slug', $slug)->first();
check($a35 && $a35->status === 'published', 'Artikel #35 visible setelah full chain');
check($a35?->tags()->count() === 6, 'Tag count = 6');
check($a35?->read_time_minutes >= 8, 'read_time ≥ 8 menit');

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 26, '#10 indeks 26 item (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh enam artikel'), '#10 teks dua puluh enam artikel');
check(str_contains($a10body, $slug), '#10 item #35');
check(str_contains($a10body, '#37') || str_contains($a10body, 'SD Card'), '#10 teaser #37');

foreach (['Article10Seeder.php', 'Article33Seeder.php'] as $file) {
    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! preg_match('/Artikel #35/', $src), "{$file}: tidak ada orphan 'Artikel #35'");
}

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    if ($target === '') continue;
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

check(str_contains($body, 'analogRead'), 'API analogRead');
check(str_contains($body, 'kodingindonesia/esp32/tanah/data'), 'Topic tanah');
check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'analogRead') || str_contains($html, 'ADC'), 'Konten kunci ter-render');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle35\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m35);
$h35 = $m35[1] ?? '';
foreach (['opcache_reset', 'SitemapService', 'view:clear', 'runDuplicateBme280Cleanup', 'PatchArticle5AdcSeeder', 'PatchArticle27LdrSeeder'] as $needle) {
    check(str_contains($h35, $needle), "Hook #35 punya {$needle}");
}
check(strpos($deploy, 'function publishArticle33') < strpos($deploy, 'function publishArticle35'), 'DeployController: #33 sebelum #35');
check(preg_match('/Publish article 35 via deploy hook \(required\)/', file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml')) === 1, 'CI hook #35 required');

foreach (['audit-article35.php', 'audit-article33.php', 'audit-article10.php', 'audit-article35-manual.php', 'audit-article35-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

$prod35 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod35 === '404';
check($preDeploy, "Production #35 belum live (HTTP {$prod35}) — expected pre-deploy", ! $preDeploy);

$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
if ($preDeploy) {
    check(str_contains($prod10html, 'dua puluh empat artikel'), 'Production #10 dua puluh empat artikel (pre-#35)');
    check(! str_contains($prod10html, $slug), 'Production #10 belum link #35');
    $prod33html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug33));
    check(! str_contains($prod33html, $slug), 'Production #33 belum hyperlink #35 (normal pre-deploy)');
}

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    check(str_contains($todo, '#35') || str_contains($todo, $slug), 'TODO.md punya #35');
    check(str_contains(file_get_contents($docsRoot . '/PRD.md'), '#35') || str_contains(file_get_contents($docsRoot . '/PRD.md'), 'ADC'), 'PRD.md #35');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
