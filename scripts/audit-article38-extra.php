<?php

/** Extra supplemental checks for #38. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article38Seeder;

$passed = 0; $failed = 0;
$slug = 'https-sertifikat-esp32-wificlientsecure-api-rest';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article38Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== EXTRA AUDIT #38 ===\n\n";

preg_match_all('#/artikel/([a-z0-9\-]+)#', $body, $matches);
foreach (array_unique($matches[1]) as $linkSlug) {
    if ($linkSlug === $slug) continue;
    $target = Article::where('slug', $linkSlug)->first();
    check($target !== null && $target->status === 'published', "Link published: {$linkSlug}");
}

check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char (' . mb_strlen($article?->seo_title ?? '') . ')');
check(str_contains($body, 'setCACert'), 'setCACert ada');
check(str_contains($body, 'WiFiClientSecure'), 'WiFiClientSecure');
check(! preg_match('/password\s*=\s*["\'][^G]/i', $body), 'Tidak ada password hardcoded di sketch');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle38\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $hook);
$hookBody = $hook[1] ?? '';
check(str_contains($hookBody, 'SitemapService'), 'Hook: SitemapService');
check(str_contains($hookBody, 'Article10Seeder'), 'Hook: re-seed Article10Seeder');
check(str_contains($hookBody, 'PatchArticle17HttpsSeeder'), 'Hook: PatchArticle17HttpsSeeder');
check(str_contains($hookBody, 'PatchArticle36HttpsSeeder'), 'Hook: PatchArticle36HttpsSeeder');
check(str_contains($hookBody, 'PatchArticle37HttpsSeeder'), 'Hook: PatchArticle37HttpsSeeder');

check(str_contains(file_get_contents(__DIR__ . '/../routes/web.php'), 'publish-article-38'), 'Route publish-article-38');
check(preg_match('/Publish article 38 via deploy hook \(required\)/', file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml')) === 1, 'CI: required hook #38');

foreach (['scripts/audit-article38.php', 'scripts/audit-article38-spotcheck.php', 'scripts/audit-article38-manual.php', 'scripts/audit-article38-paranoid.php', 'scripts/audit-article38-gapscan.php'] as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "Audit file: {$f}");
}

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    $prd = file_get_contents($docs . '/PRD.md');
    $roadmap = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) || str_contains($todo, '#38'), 'TODO.md konsisten');
    check(str_contains($prd, $slug) || str_contains($prd, '#38'), 'PRD.md konsisten');
    check(str_contains($roadmap, $slug) || str_contains($roadmap, '#38'), 'Roadmap konsisten');
    check(str_contains($roadmap, '#39') || str_contains($roadmap, 'greenhouse'), 'Roadmap teaser #39');
}

$patch = file_get_contents(__DIR__ . '/../database/seeders/PatchArticle17HttpsSeeder.php');
check(str_contains($patch, 'str_contains($article->body') || str_contains($patch, 'str_contains($body'), 'Patch #17 idempotent guard');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
