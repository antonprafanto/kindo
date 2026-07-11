<?php

/**
 * Gap-scan #30 — cek tambahan di luar audit utama (tidak seed ulang).
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article30Seeder;

$passed = 0;
$failed = 0;
$slug = 'esp32-firebase-realtime-database-sensor-cloud';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article30Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

$article = Article::where('slug', $slug)->first();

echo "=== GAP-SCAN #30 (lapisan ekstra) ===\n\n";

echo "--- Metadata ---\n\n";
check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char (' . mb_strlen($article?->seo_title ?? '') . ')');
check(str_contains($body, 'GANTI_FIREBASE_USER_PASSWORD'), 'Placeholder GANTI_FIREBASE_USER_PASSWORD');
check(str_contains($body, 'GANTI_FIREBASE_USER_EMAIL'), 'Placeholder GANTI_FIREBASE_USER_EMAIL');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder GANTI_PASSWORD_MQTT (tabel migrasi)');
check(str_contains($body, 'kindo_esp32'), 'User MQTT kindo_esp32 di tabel migrasi');
check($article?->cover_image === null || $article?->cover_image === '', 'Cover belum di-set (expected pre-upload)');

echo "\n--- Infrastruktur file ---\n\n";
$files = [
    'database/seeders/Article30Seeder.php',
    'database/seeders/PatchArticle4FirebaseSeeder.php',
    'scripts/audit-article30.php',
    'scripts/audit-article30-spotcheck.php',
    'scripts/audit-article30-manual.php',
    'scripts/audit-article30-paranoid.php',
];
foreach ($files as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "File ada: {$f}");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($deploy, 'function publishArticle30'), 'DeployController publishArticle30');
check(str_contains($routes, 'publish-article-30'), 'Route publish-article-30');
check(str_contains($yml, 'publish-article-30'), 'CI publish-article-30');

echo "\n--- HTTP render ekstra ---\n\n";
$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check(str_contains($html, 'og:title') || str_contains($html, 'property="og:title"'), 'OG title ada');
check(str_contains($html, 'og:description') || str_contains($html, 'property="og:description"'), 'OG description ada');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(str_contains($html, 'DHT22') || str_contains($html, 'dht'), 'Sensor DHT22 ter-render');

echo "\n--- Docs sync ---\n\n";
$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    $prd = file_get_contents($docs . '/PRD.md');
    $road = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) && str_contains($todo, 'siap deploy'), 'TODO.md #30 siap deploy');
    check(str_contains($prd, 'Firebase') && str_contains($prd, '#30'), 'PRD.md #30 Firebase');
    check(str_contains($road, $slug), 'roadmap slug #30');
    check(str_contains($road, '#31') || str_contains($road, 'FreeRTOS'), 'roadmap teaser #31');
} else {
    check(false, 'Docs kindo_cursorv2 tidak ditemukan');
}

echo "\n--- Production ---\n\n";
$code = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
check($code === '404', "Production pre-deploy HTTP 404 (got {$code})");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
