<?php

/**
 * Manual supplemental audit #31.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'freertos-esp32-multi-task-sensor-wifi-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #31 ===\n\n";

foreach ([
    'Article31Seeder', 'Article30Seeder', 'Article10Seeder',
    'PatchArticle9FreeRTOSSeeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle31'), 'DeployController publishArticle31');
check(str_contains($deploy, 'PatchArticle9FreeRTOSSeeder'), 'Hook PatchArticle9FreeRTOSSeeder');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 31 via deploy hook \(required\)/', $yml) === 1, 'CI hook #31 required');
check(strpos($yml, 'publish-article-30') < strpos($yml, 'publish-article-31'), 'CI: hook #30 sebelum #31');

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
check(substr_count($a10body, '<li><strong><a href="/artikel/') === 22, '#10 indeks 22 item');
check(str_contains($a10body, 'dua puluh dua artikel'), '#10 teks dua puluh dua artikel');
check(str_contains($a10body, $slug), '#10 link #31');
check(str_contains($a10body, '#32') || str_contains($a10body, 'Bluetooth'), '#10 teaser #32');

check(str_contains(Article::where('slug', 'esp32-firebase-realtime-database-sensor-cloud')->value('body') ?? '', $slug), '#30 → #31');
check(str_contains(Article::where('slug', 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek')->value('body') ?? '', $slug), '#9 → #31');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
