<?php

/** Gap-scan #35. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article35Seeder;

$passed = 0; $failed = 0;
$slug = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article35Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== GAP-SCAN #35 ===\n\n";
check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');
check(str_contains($body, 'constrain'), 'Validasi constrain persen');
check(str_contains($body, 'readFiltered') || str_contains($body, 'readSoilFiltered'), 'Filter noise ADC ada');
check($article?->cover_image === null || $article?->cover_image === '', 'Cover belum di-set');
foreach ([
    'database/seeders/Article35Seeder.php',
    'database/seeders/PatchArticle5AdcSeeder.php',
    'database/seeders/PatchArticle27LdrSeeder.php',
    'scripts/audit-article35.php',
    'scripts/audit-article35-paranoid.php',
] as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "File ada: {$f}");
}
$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle35'), 'DeployController publishArticle35');
$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check(str_contains($html, 'og:title') || str_contains($html, 'property="og:title"'), 'OG title ada');
$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    check(str_contains(file_get_contents($docs . '/TODO.md'), $slug), 'TODO.md #35');
    check(str_contains(file_get_contents($docs . '/PRD.md'), 'ADC') || str_contains(file_get_contents($docs . '/PRD.md'), '#35'), 'PRD.md #35');
}
$code = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
check($code === '404', "Production pre-deploy HTTP 404 (got {$code})");
$prod10 = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
check(str_contains($prod10, 'dua puluh empat artikel'), 'Production #10 masih 24 artikel (pre-#35)');
check(! str_contains($prod10, $slug), 'Production #10 belum link #35');
echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
