<?php

/** Extra supplemental checks for #36 — links, seo, infra parity. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article36Seeder;

$passed = 0;
$failed = 0;
$slug = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article36Seeder::class);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== EXTRA AUDIT #36 ===\n\n";

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
check(str_contains($body, 'Spesifikasi Teknis'), 'Section spesifikasi ada');
check(str_contains($body, 'esp8266/dht22/data'), 'Topic esp8266 publish');
check(! preg_match('/password\s*=\s*["\'][^G]/i', $body), 'Tidak ada password hardcoded di sketch');
check(str_contains($body, 'rel=') || ! str_contains($body, 'target="_blank"'), 'Link eksternal aman (jika ada)');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle36\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $hook);
$hookBody = $hook[1] ?? '';
check(str_contains($hookBody, 'SitemapService'), 'Hook: SitemapService');
check(str_contains($hookBody, 'Article10Seeder'), 'Hook: re-seed Article10Seeder');
check(str_contains($hookBody, 'PatchArticle1Esp8266Seeder'), 'Hook: PatchArticle1Esp8266Seeder');
check(str_contains($hookBody, 'PatchArticle35Esp8266Seeder'), 'Hook: PatchArticle35Esp8266Seeder');

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, "publish-article-36"), 'Route publish-article-36');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 36 via deploy hook \(required\)/', $yml) === 1, 'CI: required hook #36');

$auditFiles = [
    'scripts/audit-article36.php',
    'scripts/audit-article36-spotcheck.php',
    'scripts/audit-article36-manual.php',
    'scripts/audit-article36-paranoid.php',
    'scripts/audit-article36-gapscan.php',
];
foreach ($auditFiles as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "Audit file: {$f}");
}

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    $prd = file_get_contents($docs . '/PRD.md');
    $roadmap = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) || str_contains($todo, '#36'), 'TODO.md konsisten');
    check(str_contains($prd, $slug) || str_contains($prd, '#36'), 'PRD.md konsisten');
    check(str_contains($roadmap, $slug) || str_contains($roadmap, '#36'), 'Roadmap konsisten');
    check(str_contains($roadmap, '#37') || str_contains($roadmap, 'SD Card'), 'Roadmap teaser #37');
}

$patch = file_get_contents(__DIR__ . '/../database/seeders/PatchArticle1Esp8266Seeder.php');
check(str_contains($patch, 'str_contains($article->body') || str_contains($patch, 'str_contains($body'), 'Patch #1 idempotent guard');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
