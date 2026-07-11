<?php

/**
 * Paranoid audit #38.
 * Usage: php scripts/audit-article38-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article38Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0; $failed = 0; $warn = 0;
$slug = 'https-sertifikat-esp32-wificlientsecure-api-rest';

function check(bool $ok, string $label, bool $warning = false): void {
    global $passed, $failed, $warn;
    if ($warning && ! $ok) { echo "⚠ {$label}\n"; $warn++; return; }
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article38Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #38 ===\n\n";

foreach (['Article38Seeder', 'Article10Seeder', 'PatchArticle17HttpsSeeder', 'PatchArticle36HttpsSeeder', 'PatchArticle37HttpsSeeder', 'RemoveDuplicateBme280Seeder'] as $cls) {
    check(Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]) === 0, "Seed {$cls} exit 0");
}

$a38 = Article::where('slug', $slug)->first();
check($a38 && $a38->status === 'published', 'Artikel #38 visible setelah full chain');
check($a38?->tags()->count() === 6, 'Tag count = 6');
check($a38?->read_time_minutes >= 8, 'read_time ≥ 8 menit');

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 28, '#10 indeks 28 item (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh delapan artikel'), '#10 teks dua puluh delapan artikel');
check(str_contains($a10body, $slug), '#10 item #38');
check(str_contains($a10body, '#39') || str_contains($a10body, 'greenhouse'), '#10 teaser #39');

foreach (['Article10Seeder.php', 'Article17Seeder.php', 'Article36Seeder.php', 'Article37Seeder.php'] as $file) {
    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! preg_match('/Artikel #38/', $src), "{$file}: tidak ada orphan 'Artikel #38'");
}

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    if ($target === '') continue;
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

check(str_contains($body, 'WiFiClientSecure'), 'WiFiClientSecure');
check(str_contains($body, 'HTTPClient'), 'HTTPClient');
check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'HTTPS') || str_contains($html, 'TLS'), 'Konten kunci ter-render');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle38\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m38);
$h38 = $m38[1] ?? '';
foreach (['opcache_reset', 'SitemapService', 'view:clear', 'runDuplicateBme280Cleanup', 'PatchArticle17HttpsSeeder', 'PatchArticle36HttpsSeeder', 'PatchArticle37HttpsSeeder'] as $needle) {
    check(str_contains($h38, $needle), "Hook #38 punya {$needle}");
}
check(strpos($deploy, 'function publishArticle37') < strpos($deploy, 'function publishArticle38'), 'DeployController: #37 sebelum #38');
check(preg_match('/Publish article 38 via deploy hook \(required\)/', file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml')) === 1, 'CI hook #38 required');

foreach (['audit-article38.php', 'audit-article37.php', 'audit-article10.php', 'audit-article38-manual.php', 'audit-article38-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

$prod38 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod38 === '404';
check($preDeploy, "Production #38 belum live (HTTP {$prod38}) — expected pre-deploy", ! $preDeploy);

$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
if ($preDeploy) {
    check(str_contains($prod10html, 'dua puluh tujuh artikel'), 'Production #10 dua puluh tujuh artikel (pre-#38)');
    check(! str_contains($prod10html, $slug), 'Production #10 belum link #38');
}

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    check(str_contains(file_get_contents($docsRoot . '/TODO.md'), '#38'), 'TODO.md punya #38');
    check(str_contains(file_get_contents($docsRoot . '/PRD.md'), '#38') || str_contains(file_get_contents($docsRoot . '/PRD.md'), 'HTTPS'), 'PRD.md #38');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
