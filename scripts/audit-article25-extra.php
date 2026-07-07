<?php

/**
 * Extra exhaustive audit #25 — regresi & edge case.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article25Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article25Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== EXTRA EXHAUSTIVE AUDIT #25 ===\n\n";

echo "--- A: Regresi audit artikel terkait ---\n\n";

$relatedAudits = [
    'audit-article20.php' => '#20',
    'audit-article10.php' => '#10',
    'audit-article11.php' => '#11',
];

foreach ($relatedAudits as $script => $label) {
    $path = __DIR__ . '/' . $script;
    if (! file_exists($path)) {
        check(false, "{$label}: skrip {$script} tidak ada");
        continue;
    }
    exec('php ' . escapeshellarg($path) . ' 2>&1', $output, $code);
    check($code === 0, "{$label}: {$script} masih lulus setelah update backlink (exit {$code})");
    if ($code !== 0) {
        echo '    ' . implode("\n    ", array_slice($output, -4)) . "\n";
    }
}

echo "\n--- B: Semua link internal #25 valid di DB ---\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article25Seeder', '--force' => true]);
$article = Article::where('slug', $slug)->first();
$dbBody = $article?->body ?? '';

preg_match_all('/href="(\/artikel\/[^"]+)"/', $dbBody, $matches);
$uniqueLinks = array_unique($matches[1]);
check(count($uniqueLinks) >= 10, 'Minimal 10 link internal unik (' . count($uniqueLinks) . ')');

foreach ($uniqueLinks as $path) {
    $targetSlug = str_replace('/artikel/', '', $path);
    if ($targetSlug === '') {
        continue;
    }
    check(Article::where('slug', $targetSlug)->exists(), "Target DB ada: {$path}");
}

echo "\n--- C: Deploy hook & CI ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$pos25 = strpos($deploy, 'publishArticle25');
$pos20 = strpos($deploy, 'publishArticle20');
check($pos25 !== false && $pos20 !== false, 'publishArticle25 + publishArticle20 ada');

$reseedClasses = [
    'Article20Seeder', 'Article10Seeder', 'Article11Seeder', 'Article7Seeder',
];
if (preg_match('/function publishArticle25\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m)) {
    foreach ($reseedClasses as $class) {
        check(str_contains($m[1], $class), "publishArticle25 re-seed {$class}");
    }
} else {
    check(false, 'publishArticle25() ditemukan di DeployController');
}

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, 'publish-article-25'), 'Route publish-article-25');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($yml, 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'), 'CI verifikasi slug #25');

echo "\n--- D: Konsistensi konten ---\n\n";

check(str_contains($body, 'mosquitto_sub'), 'Perintah mosquitto_sub uji coba');
check(str_contains($body, 'esp_now_send'), 'API esp_now_send');
check(str_contains($body, 'esp_now_register_recv_cb'), 'Callback receive');
check(str_contains($body, 'GANTI_MAC_GATEWAY'), 'Placeholder MAC gateway');
check(str_contains($body, '250 byte'), 'Batas payload ESP-NOW');

$words = str_word_count(strip_tags($body));
check($words >= 1300 && $words <= 4000, "Word count wajar ({$words})");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
