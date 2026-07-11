<?php

/** Extra supplemental checks for #35 — links, seo, infra parity vs #33. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article35Seeder;

$passed = 0;
$failed = 0;
$slug = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article35Seeder::class);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== EXTRA AUDIT #35 ===\n\n";

preg_match_all('#/artikel/([a-z0-9\-]+)#', $body, $matches);
$slugs = array_unique($matches[1]);
foreach ($slugs as $linkSlug) {
    if ($linkSlug === $slug) {
        continue;
    }
    $target = Article::where('slug', $linkSlug)->first();
    check($target !== null && $target->status === 'published', "Link published: {$linkSlug}");
}

check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char (' . mb_strlen($article?->seo_title ?? '') . ')');
check(mb_strlen($article?->title ?? '') <= 120, 'title panjang wajar');
check(str_contains($body, 'Capacitive vs Resistive') || str_contains($body, 'capacitive'), 'Section probe type ada');
check(str_contains($body, 'Kalibrasi Soil Moisture'), 'Section kalibrasi ada');
check(str_contains($body, 'cahaya/data'), 'Topic cahaya publish');
check(! preg_match('/password\s*=\s*["\'][^G]/i', $body), 'Tidak ada password hardcoded di sketch');
check(str_contains($body, 'rel=') || ! str_contains($body, 'target="_blank"'), 'Link eksternal aman (jika ada)');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle35\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $hook);
$hookBody = $hook[1] ?? '';
check(str_contains($hookBody, 'SitemapService'), 'Hook: SitemapService');
check(str_contains($hookBody, 'Article33Seeder'), 'Hook: re-seed Article33Seeder');
check(str_contains($hookBody, 'Article10Seeder'), 'Hook: re-seed Article10Seeder');
check(str_contains($hookBody, 'PatchArticle5AdcSeeder'), 'Hook: PatchArticle5AdcSeeder');
check(str_contains($hookBody, 'PatchArticle27LdrSeeder'), 'Hook: PatchArticle27LdrSeeder');

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, "publish-article-35"), 'Route publish-article-35');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 35 via deploy hook \(required\)/', $yml) === 1, 'CI: required hook #35');

$auditFiles = [
    'scripts/audit-article35.php',
    'scripts/audit-article35-spotcheck.php',
    'scripts/audit-article35-manual.php',
    'scripts/audit-article35-paranoid.php',
    'scripts/audit-article35-gapscan.php',
];
foreach ($auditFiles as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "Audit file: {$f}");
}

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    $prd = file_get_contents($docs . '/PRD.md');
    $roadmap = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) || str_contains($todo, '#35'), 'TODO.md konsisten');
    check(str_contains($prd, $slug) || str_contains($prd, '#35'), 'PRD.md konsisten');
    check(str_contains($roadmap, $slug) || str_contains($roadmap, '#35'), 'Roadmap konsisten');
    check(str_contains($roadmap, '#36'), 'Roadmap teaser #36');
}

$patch = file_get_contents(__DIR__ . '/../database/seeders/PatchArticle5AdcSeeder.php');
check(str_contains($patch, 'str_contains($article->body') || str_contains($patch, 'str_contains($body'), 'Patch #5 idempotent guard');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
