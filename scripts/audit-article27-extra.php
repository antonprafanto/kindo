<?php

/**
 * Extra exhaustive audit #27 — regresi & edge case.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article27Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article27Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== EXTRA EXHAUSTIVE AUDIT #27 ===\n\n";

echo "--- A: Regresi audit artikel terkait ---\n\n";

$relatedAudits = [
    'audit-article26.php' => '#26',
    'audit-article10.php' => '#10',
    'audit-article20.php' => '#20',
    'audit-article6.php'  => '#6',
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

echo "\n--- B: Semua link internal #27 valid di DB ---\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article27Seeder', '--force' => true]);
$article = Article::where('slug', $slug)->first();
$dbBody = $article?->body ?? '';

preg_match_all('/href="(\/artikel\/[^"]+)"/', $dbBody, $matches);
$uniqueLinks = array_unique($matches[1]);
check(count($uniqueLinks) >= 8, 'Minimal 8 link internal unik (' . count($uniqueLinks) . ')');

foreach ($uniqueLinks as $path) {
    $targetSlug = str_replace('/artikel/', '', $path);
    if ($targetSlug === '') {
        continue;
    }
    check(Article::where('slug', $targetSlug)->exists(), "Target DB ada: {$path}");
}

echo "\n--- C: Deploy hook & CI ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$pos27 = strpos($deploy, 'publishArticle27');
$pos26 = strpos($deploy, 'publishArticle26');
check($pos27 !== false && $pos26 !== false, 'publishArticle27 + publishArticle26 ada');

$reseedClasses = [
    'Article26Seeder', 'Article10Seeder', 'Article6Seeder', 'Article20Seeder',
];
if (preg_match('/function publishArticle27\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m)) {
    foreach ($reseedClasses as $class) {
        check(str_contains($m[1], $class), "publishArticle27 re-seed {$class}");
    }
} else {
    check(false, 'publishArticle27() ditemukan di DeployController');
}

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, 'publish-article-27'), 'Route publish-article-27');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($yml, 'esp32-cam-streaming-mjpeg-capture-foto-wifi'), 'CI verifikasi slug #27');

echo "\n--- D: Konsistensi konten ---\n\n";

check(str_contains($body, 'mosquitto_sub'), 'Perintah mosquitto_sub uji hybrid');
check(str_contains($body, 'esp_camera_init'), 'API esp_camera_init');
check(str_contains($body, 'multipart/x-mixed-replace'), 'Header MJPEG multipart');
check(str_contains($body, 'OV2640'), 'Sensor OV2640 dijelaskan');
check(str_contains($body, 'AI Thinker'), 'Board AI Thinker');

$words = str_word_count(strip_tags($body));
check($words >= 1300 && $words <= 4000, "Word count wajar ({$words})");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
