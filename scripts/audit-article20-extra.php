<?php

/**
 * Extra exhaustive audit #20 — regresi & edge case.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article20Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article20Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== EXTRA EXHAUSTIVE AUDIT #20 ===\n\n";

echo "--- A: Regresi audit artikel terkait ---\n\n";

$relatedAudits = [
    'audit-article19.php' => '#19',
    'audit-article18.php' => '#18',
    'audit-article10.php' => '#10',
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

echo "\n--- B: Semua link internal #20 valid di DB ---\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article20Seeder', '--force' => true]);
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
$pos20 = strpos($deploy, 'publishArticle20');
$pos19 = strpos($deploy, 'publishArticle19');
check($pos20 !== false && $pos19 !== false, 'publishArticle20 + publishArticle19 ada');

$reseedClasses = [
    'Article19Seeder', 'Article18Seeder', 'Article10Seeder',
    'Article7Seeder', 'Article6Seeder', 'Article17Seeder', 'Article16Seeder',
];
if (preg_match('/function publishArticle20\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m)) {
    foreach ($reseedClasses as $class) {
        check(str_contains($m[1], $class), "publishArticle20 re-seed {$class}");
    }
} else {
    check(false, 'publishArticle20() ditemukan di DeployController');
}

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, 'publish-article-20'), 'Route publish-article-20');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($yml, 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'), 'CI verifikasi slug #20');

echo "\n--- D: Konsistensi konten ---\n\n";

check(str_contains($body, 'mosquitto_sub'), 'Perintah mosquitto_sub uji coba');
check(str_contains($body, 'curl http://192.168.1.100/api/data'), 'Perintah curl uji REST');
check(str_contains($body, 'Jangan pakai') && str_contains($body, 'test.mosquitto.org'), 'Peringatan jangan pakai broker publik produksi');
check(str_contains($body, 'sensor_readings'), 'Referensi tabel MySQL #18');

$words = str_word_count(strip_tags($body));
check($words >= 1300 && $words <= 3500, "Word count wajar ({$words})");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
