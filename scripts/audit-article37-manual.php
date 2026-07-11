<?php



/** Manual audit #37 — hook, route, CI parity. */



require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();



use App\Models\Article;

use Illuminate\Support\Facades\Artisan;



$passed = 0; $failed = 0;

$slug = 'sd-card-spi-esp32-logging-data-sensor-offline';



function check(bool $ok, string $label): void {

    global $passed, $failed;

    echo ($ok ? '✓' : '✗') . " {$label}\n";

    $ok ? $passed++ : $failed++;

}



echo "=== MANUAL AUDIT #37 ===\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');

$routes = file_get_contents(__DIR__ . '/../routes/web.php');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');

check(str_contains($deploy, 'publishArticle37'), 'DeployController publishArticle37');

check(str_contains($routes, 'publish-article-37'), 'Route publish-article-37');

check(str_contains($yml, 'publish-article-37'), 'CI publish-article-37');

check(strpos($yml, 'publish-article-36') < strpos($yml, 'publish-article-37'), 'CI: hook #36 sebelum #37');

check(strpos($deploy, 'function publishArticle36') < strpos($deploy, 'function publishArticle37'), 'DeployController: #36 hook sebelum #37');



Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article37Seeder', '--force' => true]);

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle36SdCardSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle27SdCardSeeder', '--force' => true]);



$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';

check(substr_count($a10body, '<li><strong><a href="/artikel/') === 28, '#10 indeks 28 item');

check(str_contains($a10body, 'dua puluh delapan artikel'), '#10 teks dua puluh delapan artikel');

check(str_contains($a10body, $slug), '#10 item #37 di indeks');

check(str_contains($a10body, '#38') || str_contains($a10body, 'HTTPS'), '#10 teaser #38 HTTPS');

check(file_exists(__DIR__ . '/../database/seeders/Article37Seeder.php'), 'Article37Seeder.php ada');

check(file_exists(__DIR__ . '/../database/seeders/PatchArticle36SdCardSeeder.php'), 'PatchArticle36SdCardSeeder.php ada');
check(file_exists(__DIR__ . '/../database/seeders/PatchArticle27SdCardSeeder.php'), 'PatchArticle27SdCardSeeder.php ada');

foreach (['audit-article37.php', 'audit-article37-spotcheck.php'] as $script) {

    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);

    check($code === 0, "{$script} OK");

}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";

exit($failed > 0 ? 1 : 0);

