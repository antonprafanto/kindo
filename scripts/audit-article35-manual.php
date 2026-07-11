<?php

/** Manual audit #35 — hook, route, CI parity. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0; $failed = 0;
$slug = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #35 ===\n\n";
$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($deploy, 'publishArticle35'), 'DeployController publishArticle35');
check(str_contains($routes, 'publish-article-35'), 'Route publish-article-35');
check(str_contains($yml, 'publish-article-35'), 'CI publish-article-35');
check(strpos($yml, 'publish-article-33') < strpos($yml, 'publish-article-35'), 'CI: hook #33 sebelum #35');
check(strpos($deploy, 'function publishArticle33') < strpos($deploy, 'function publishArticle35'), 'DeployController: #33 hook sebelum #35');

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article35Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article33Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle5AdcSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle27LdrSeeder', '--force' => true]);

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
check(substr_count($a10body, '<li><strong><a href="/artikel/') === 26, '#10 indeks 26 item');
check(str_contains($a10body, 'dua puluh enam artikel'), '#10 teks dua puluh enam artikel');
check(str_contains($a10body, $slug), '#10 item #35 di indeks');
check(str_contains($a10body, '#37') || str_contains($a10body, 'SD Card'), '#10 teaser #37 SD Card');
check(file_exists(__DIR__ . '/../database/seeders/Article35Seeder.php'), 'Article35Seeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle5AdcSeeder.php'), 'PatchArticle5AdcSeeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle27LdrSeeder.php'), 'PatchArticle27LdrSeeder.php ada');
foreach (['audit-article35.php', 'audit-article35-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} OK");
}
echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
