<?php

/**
 * Gap-scan #31 — cek tambahan di luar audit utama.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article31Seeder;

$passed = 0;
$failed = 0;
$slug = 'freertos-esp32-multi-task-sensor-wifi-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article31Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== GAP-SCAN #31 (lapisan ekstra) ===\n\n";

echo "--- Metadata ---\n\n";
check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char (' . mb_strlen($article?->seo_title ?? '') . ')');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder GANTI_PASSWORD_WIFI');
check(str_contains($body, 'uxTaskGetStackHighWaterMark'), 'Stack watermark debug');
check($article?->cover_image === null || $article?->cover_image === '', 'Cover belum di-set (expected)');

echo "\n--- Infrastruktur file ---\n\n";
foreach ([
    'database/seeders/Article31Seeder.php',
    'database/seeders/PatchArticle9FreeRTOSSeeder.php',
    'scripts/audit-article31.php',
    'scripts/audit-article31-spotcheck.php',
    'scripts/audit-article31-manual.php',
    'scripts/audit-article31-paranoid.php',
] as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "File ada: {$f}");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle31'), 'DeployController publishArticle31');
check(str_contains(file_get_contents(__DIR__ . '/../routes/web.php'), 'publish-article-31'), 'Route publish-article-31');

echo "\n--- HTTP render ekstra ---\n\n";
$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check(str_contains($html, 'og:title') || str_contains($html, 'property="og:title"'), 'OG title ada');
check(str_contains($html, 'xTaskCreatePinnedToCore'), 'API task ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');

echo "\n--- Docs sync ---\n\n";
$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    $prd = file_get_contents($docs . '/PRD.md');
    $road = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) && str_contains($todo, 'siap deploy'), 'TODO.md #31 siap deploy');
    check(str_contains($prd, 'FreeRTOS') && str_contains($prd, '#31'), 'PRD.md #31');
    check(str_contains($road, $slug), 'roadmap slug #31');
    check(str_contains($road, 'Tier 1 inti') || str_contains($road, '#11–#31'), 'roadmap Tier 1 inti');
} else {
    check(false, 'Docs kindo_cursorv2 tidak ditemukan');
}

echo "\n--- Production ---\n\n";
$code = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
check($code === '404', "Production pre-deploy HTTP 404 (got {$code})");

$prod10 = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
check(str_contains($prod10, 'dua puluh satu artikel'), 'Production #10 masih 21 artikel (pre-#31)');
check(! str_contains($prod10, $slug), 'Production #10 belum link #31');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
