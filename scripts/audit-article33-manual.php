<?php

/** Manual audit #33 — hook, route, CI parity. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0; $failed = 0;
$slug = 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #33 ===\n\n";
$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($deploy, 'publishArticle33'), 'DeployController publishArticle33');
check(str_contains($routes, 'publish-article-33'), 'Route publish-article-33');
check(str_contains($yml, 'publish-article-33'), 'CI publish-article-33');
check(strpos($yml, 'publish-article-32') < strpos($yml, 'publish-article-33'), 'CI: hook #32 sebelum #33');
check(strpos($deploy, 'function publishArticle32') < strpos($deploy, 'function publishArticle33'), 'DeployController: #32 hook sebelum #33');

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article33Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article32Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle8ServoSeeder', '--force' => true]);

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
check(substr_count($a10body, '<li><strong><a href="/artikel/') === 24, '#10 indeks 24 item');
check(str_contains($a10body, 'dua puluh empat artikel'), '#10 teks dua puluh empat artikel');
check(str_contains($a10body, $slug), '#10 item #33 di indeks');
check(str_contains($a10body, '#35') || str_contains($a10body, 'soil moisture'), '#10 teaser #35 ADC');
check(file_exists(__DIR__ . '/../database/seeders/Article33Seeder.php'), 'Article33Seeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle8ServoSeeder.php'), 'PatchArticle8ServoSeeder.php ada');
foreach (['audit-article33.php', 'audit-article33-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} OK");
}
echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
