<?php

/** Manual audit #38 — hook, route, CI parity. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0; $failed = 0;
$slug = 'https-sertifikat-esp32-wificlientsecure-api-rest';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #38 ===\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');

check(str_contains($deploy, 'publishArticle38'), 'DeployController publishArticle38');
check(str_contains($routes, 'publish-article-38'), 'Route publish-article-38');
check(str_contains($yml, 'publish-article-38'), 'CI publish-article-38');
check(strpos($yml, 'publish-article-37') < strpos($yml, 'publish-article-38'), 'CI: hook #37 sebelum #38');
check(strpos($deploy, 'function publishArticle37') < strpos($deploy, 'function publishArticle38'), 'DeployController: #37 hook sebelum #38');

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article38Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle17HttpsSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle36HttpsSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle37HttpsSeeder', '--force' => true]);

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
check(substr_count($a10body, '<li><strong><a href="/artikel/') === 28, '#10 indeks 28 item');
check(str_contains($a10body, 'dua puluh delapan artikel'), '#10 teks dua puluh delapan artikel');
check(str_contains($a10body, $slug), '#10 item #38 di indeks');
check(str_contains($a10body, '#39') || str_contains($a10body, 'greenhouse'), '#10 teaser #39 capstone');

check(file_exists(__DIR__ . '/../database/seeders/Article38Seeder.php'), 'Article38Seeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle17HttpsSeeder.php'), 'PatchArticle17HttpsSeeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle36HttpsSeeder.php'), 'PatchArticle36HttpsSeeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle37HttpsSeeder.php'), 'PatchArticle37HttpsSeeder.php ada');

foreach (['audit-article38.php', 'audit-article38-spotcheck.php'] as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} OK");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
