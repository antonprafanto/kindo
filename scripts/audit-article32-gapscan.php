<?php

/**
 * Gap-scan #32 — cek tambahan di luar audit utama.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article32Seeder;

$passed = 0;
$failed = 0;
$slug = 'bluetooth-esp32-ble-kirim-data-sensor-smartphone';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article32Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== GAP-SCAN #32 (lapisan ekstra) ===\n\n";

echo "--- Metadata ---\n\n";
check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char (' . mb_strlen($article?->seo_title ?? '') . ')');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder GANTI_PASSWORD_MQTT');
check(str_contains($body, 'deviceConnected'), 'Flag deviceConnected');
check($article?->cover_image === null || $article?->cover_image === '', 'Cover belum di-set (expected)');

echo "\n--- Infrastruktur file ---\n\n";
foreach ([
    'database/seeders/Article32Seeder.php',
    'database/seeders/PatchArticle1BluetoothSeeder.php',
    'scripts/audit-article32.php',
    'scripts/audit-article32-spotcheck.php',
    'scripts/audit-article32-manual.php',
    'scripts/audit-article32-paranoid.php',
] as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "File ada: {$f}");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle32'), 'DeployController publishArticle32');
check(str_contains(file_get_contents(__DIR__ . '/../routes/web.php'), 'publish-article-32'), 'Route publish-article-32');

echo "\n--- HTTP render ekstra ---\n\n";
$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check(str_contains($html, 'og:title') || str_contains($html, 'property="og:title"'), 'OG title ada');
check(str_contains($html, 'KindoESP32-DHT22'), 'Nama perangkat ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');

echo "\n--- Docs sync ---\n\n";
$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    $prd = file_get_contents($docs . '/PRD.md');
    $road = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) && (str_contains($todo, 'siap deploy') || str_contains($todo, '[~]')), 'TODO.md #32 drafting');
    check(str_contains($prd, 'BLE') || str_contains($prd, '#32'), 'PRD.md #32');
    check(str_contains($road, $slug) || str_contains($road, 'BLE'), 'roadmap #32');
    check(str_contains($road, 'Tier 2') || str_contains($road, '#32'), 'roadmap Tier 2');
} else {
    check(false, 'Docs kindo_cursorv2 tidak ditemukan');
}

echo "\n--- Production ---\n\n";
$code = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
check($code === '404', "Production pre-deploy HTTP 404 (got {$code})");

$prod10 = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
check(str_contains($prod10, 'dua puluh dua artikel'), 'Production #10 masih 22 artikel (pre-#32)');
check(! str_contains($prod10, $slug), 'Production #10 belum link #32');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
