<?php

/**
 * Manual audit #32 — hook, route, CI parity.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug = 'bluetooth-esp32-ble-kirim-data-sensor-smartphone';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #32 ===\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');

check(str_contains($deploy, 'publishArticle32'), 'DeployController publishArticle32');
check(str_contains($routes, 'publish-article-32'), 'Route publish-article-32');
check(str_contains($yml, 'publish-article-32'), 'CI publish-article-32');
check(strpos($yml, 'publish-article-31') < strpos($yml, 'publish-article-32'), 'CI: hook #31 sebelum #32');
check(strpos($deploy, 'function publishArticle31') < strpos($deploy, 'function publishArticle32'), 'DeployController: #31 hook sebelum #32');

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article32Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article31Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle1BluetoothSeeder', '--force' => true]);

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
check(substr_count($a10body, '<li><strong><a href="/artikel/') === 23, '#10 indeks 23 item');
check(str_contains($a10body, 'dua puluh tiga artikel'), '#10 teks dua puluh tiga artikel');
check(str_contains($a10body, $slug), '#10 item #32 di indeks');
check(str_contains($a10body, '#33') || str_contains($a10body, 'Servo'), '#10 teaser #33 Servo');

check(file_exists(__DIR__ . '/../database/seeders/Article32Seeder.php'), 'Article32Seeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle1BluetoothSeeder.php'), 'PatchArticle1BluetoothSeeder.php ada');

foreach (['audit-article32.php', 'audit-article32-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} OK");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
