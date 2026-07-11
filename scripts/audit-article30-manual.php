<?php

/**
 * Manual supplemental audit #30.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'esp32-firebase-realtime-database-sensor-cloud';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #30 ===\n\n";

foreach ([
    'Article30Seeder', 'Article29Seeder', 'Article10Seeder',
    'PatchArticle4FirebaseSeeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle30'), 'DeployController publishArticle30');
check(str_contains($deploy, 'PatchArticle4FirebaseSeeder'), 'Hook PatchArticle4FirebaseSeeder');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 30 via deploy hook \(required\)/', $yml) === 1, 'CI hook #30 required');
check(strpos($yml, 'publish-article-29') < strpos($yml, 'publish-article-30'), 'CI: hook #29 sebelum #30');

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
check(substr_count($a10body, '<li><strong><a href="/artikel/') === 21, '#10 indeks 21 item');
check(str_contains($a10body, 'dua puluh satu artikel'), '#10 teks dua puluh satu artikel');
check(str_contains($a10body, $slug), '#10 link #30');
check(str_contains($a10body, '#31') || str_contains($a10body, 'FreeRTOS'), '#10 teaser #31');

check(str_contains(Article::where('slug', 'migrasi-platformio-esp32-vscode-project-rapi')->value('body') ?? '', $slug), '#29 → #30');
check(str_contains(Article::where('slug', 'menghubungkan-esp32-wifi-kirim-data-server')->value('body') ?? '', $slug), '#4 → #30');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
