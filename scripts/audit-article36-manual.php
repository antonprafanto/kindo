<?php

/** Manual audit #36 — hook, route, CI parity. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0; $failed = 0;
$slug = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #36 ===\n\n";
$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($deploy, 'publishArticle36'), 'DeployController publishArticle36');
check(str_contains($routes, 'publish-article-36'), 'Route publish-article-36');
check(str_contains($yml, 'publish-article-36'), 'CI publish-article-36');
check(strpos($yml, 'publish-article-35') < strpos($yml, 'publish-article-36'), 'CI: hook #35 sebelum #36');
check(strpos($deploy, 'function publishArticle35') < strpos($deploy, 'function publishArticle36'), 'DeployController: #35 hook sebelum #36');

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article36Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle1Esp8266Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle35Esp8266Seeder', '--force' => true]);

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
check(substr_count($a10body, '<li><strong><a href="/artikel/') === 26, '#10 indeks 26 item');
check(str_contains($a10body, 'dua puluh enam artikel'), '#10 teks dua puluh enam artikel');
check(str_contains($a10body, $slug), '#10 item #36 di indeks');
check(str_contains($a10body, '#37') || str_contains($a10body, 'SD Card'), '#10 teaser #37 SD Card');
check(file_exists(__DIR__ . '/../database/seeders/Article36Seeder.php'), 'Article36Seeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle1Esp8266Seeder.php'), 'PatchArticle1Esp8266Seeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle35Esp8266Seeder.php'), 'PatchArticle35Esp8266Seeder.php ada');
foreach (['audit-article36.php', 'audit-article36-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} OK");
}
echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
