<?php

/**
 * Manual supplemental audit #29.
 * Usage: php scripts/audit-article29-manual.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'migrasi-platformio-esp32-vscode-project-rapi';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #29 ===\n\n";

foreach ([
    'Article29Seeder', 'Article28Seeder', 'Article10Seeder',
    'PatchArticle2PlatformioSeeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

foreach ([
    'Article28Seeder.php', 'Article10Seeder.php', 'ArticleSeeder.php',
] as $file) {
    $content = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! str_contains($content, 'Artikel #29'), "{$file}: tidak ada teks orphan 'Artikel #29'");
}

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'    => '#10',
    'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard' => '#28',
    'cara-install-arduino-ide-setup-esp32-board-manager'  => '#2',
];

foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    check(str_contains($b, $href) || str_contains($b, $slug), "{$lbl} hyperlink → #29");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle29'), 'DeployController publishArticle29');
check(str_contains($deploy, 'Article28Seeder'), 'Hook re-seed Article28Seeder');
check(str_contains($deploy, 'PatchArticle2PlatformioSeeder'), 'Hook PatchArticle2PlatformioSeeder');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 29 via deploy hook \(required\)/', $yml) === 1, 'CI hook #29 required');
check(strpos($yml, 'publish-article-28') < strpos($yml, 'publish-article-29'), 'CI: hook #28 sebelum #29');

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, 'publish-article-29'), 'Route publish-article-29');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
$a10body = $a10?->body ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 21, '#10 indeks punya 21 item live (' . $indexItems . ')');
check(str_contains($a10body, 'dua puluh satu artikel'), '#10 indeks dua puluh satu artikel');
check(str_contains($a10body, $slug), '#10 punya link #29 di indeks');
check(str_contains($a10body, '#30') || str_contains($a10body, 'Firebase'), '#10 teaser #30 Firebase');

$a28 = Article::where('slug', 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard')->first();
check(str_contains($a28?->body ?? '', $slug), '#28 hyperlink → #29');

$a2 = Article::where('slug', 'cara-install-arduino-ide-setup-esp32-board-manager')->first();
check(str_contains($a2?->body ?? '', $slug), '#2 hyperlink → #29');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
