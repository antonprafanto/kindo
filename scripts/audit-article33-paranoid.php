<?php

/**
 * Paranoid audit #33.
 * Usage: php scripts/audit-article33-paranoid.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article33Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0; $failed = 0; $warn = 0;
$slug = 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi';
$slug32 = 'bluetooth-esp32-ble-kirim-data-sensor-smartphone';

function check(bool $ok, string $label, bool $warning = false): void {
    global $passed, $failed, $warn;
    if ($warning && ! $ok) { echo "⚠ {$label}\n"; $warn++; return; }
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article33Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());

echo "=== PARANOID AUDIT #33 ===\n\n";

foreach (['Article33Seeder', 'Article32Seeder', 'Article10Seeder', 'PatchArticle8ServoSeeder', 'RemoveDuplicateBme280Seeder'] as $cls) {
    check(Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]) === 0, "Seed {$cls} exit 0");
}

$a33 = Article::where('slug', $slug)->first();
check($a33 && $a33->status === 'published', 'Artikel #33 visible setelah full chain');
check($a33?->tags()->count() === 6, 'Tag count = 6');
check($a33?->read_time_minutes >= 8, 'read_time ≥ 8 menit');

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 24, '#10 indeks 24 item (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh empat artikel'), '#10 teks dua puluh empat artikel');
check(str_contains($a10body, $slug), '#10 item #33');
check(str_contains($a10body, '#35') || str_contains($a10body, 'soil moisture'), '#10 teaser #35');

foreach (['Article10Seeder.php', 'Article32Seeder.php'] as $file) {
    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! preg_match('/Artikel #33/', $src), "{$file}: tidak ada orphan 'Artikel #33'");
}

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    if ($target === '') continue;
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

check(str_contains($body, 'ESP32Servo'), 'Library ESP32Servo');
check(str_contains($body, 'kodingindonesia/esp32/servo/sudut'), 'Topic servo');
check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'ESP32Servo'), 'Konten kunci ter-render');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle33\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m33);
$h33 = $m33[1] ?? '';
foreach (['opcache_reset', 'SitemapService', 'view:clear', 'runDuplicateBme280Cleanup', 'PatchArticle8ServoSeeder'] as $needle) {
    check(str_contains($h33, $needle), "Hook #33 punya {$needle}");
}
check(strpos($deploy, 'function publishArticle32') < strpos($deploy, 'function publishArticle33'), 'DeployController: #32 sebelum #33');
check(preg_match('/Publish article 33 via deploy hook \(required\)/', file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml')) === 1, 'CI hook #33 required');

foreach (['audit-article33.php', 'audit-article32.php', 'audit-article10.php', 'audit-article33-manual.php', 'audit-article33-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

$prod33 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
$preDeploy = $prod33 === '404';
check($preDeploy, "Production #33 belum live (HTTP {$prod33}) — expected pre-deploy", ! $preDeploy);

$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
if ($preDeploy) {
    check(str_contains($prod10html, 'dua puluh tiga artikel'), 'Production #10 dua puluh tiga artikel (pre-#33)');
    check(! str_contains($prod10html, $slug), 'Production #10 belum link #33');
    $prod32html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug32));
    check(! str_contains($prod32html, $slug), 'Production #32 belum hyperlink #33 (normal pre-deploy)');
}

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    check(str_contains($todo, '#33') || str_contains($todo, $slug), 'TODO.md punya #33');
    check(str_contains(file_get_contents($docsRoot . '/PRD.md'), '#33') || str_contains(file_get_contents($docsRoot . '/PRD.md'), 'Servo'), 'PRD.md #33');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
