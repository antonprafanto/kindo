<?php

/** Extra supplemental checks for #33 — links, seo, infra parity vs #32. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article33Seeder;

$passed = 0;
$failed = 0;
$slug = 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article33Seeder::class);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== EXTRA AUDIT #33 ===\n\n";

// All internal links resolve to published articles
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
check(str_contains($body, 'moveServoSmooth') || str_contains($body, 'Gerakan Bertahap'), 'Section anti-jerk ada');
check(str_contains($body, 'Kalibrasi Pulse'), 'Section kalibrasi ada');
check(str_contains($body, 'servo/status'), 'Topic status publish balik');
check(! preg_match('/password\s*=\s*["\'][^G]/i', $body), 'Tidak ada password hardcoded di sketch');
check(str_contains($body, 'rel=') || ! str_contains($body, 'target="_blank"'), 'Link eksternal aman (jika ada)');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle33\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $hook);
$hookBody = $hook[1] ?? '';
check(str_contains($hookBody, 'SitemapService'), 'Hook: SitemapService');
check(str_contains($hookBody, 'Article32Seeder'), 'Hook: re-seed Article32Seeder');
check(str_contains($hookBody, 'Article10Seeder'), 'Hook: re-seed Article10Seeder');
check(str_contains($hookBody, 'PatchArticle8ServoSeeder'), 'Hook: PatchArticle8ServoSeeder');

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, "publish-article-33"), 'Route publish-article-33');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 33 via deploy hook \(required\)/', $yml) === 1, 'CI: required hook #33');

$auditFiles = [
    'scripts/audit-article33.php',
    'scripts/audit-article33-spotcheck.php',
    'scripts/audit-article33-manual.php',
    'scripts/audit-article33-paranoid.php',
    'scripts/audit-article33-gapscan.php',
];
foreach ($auditFiles as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "Audit file: {$f}");
}

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    $prd = file_get_contents($docs . '/PRD.md');
    $roadmap = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) && str_contains($todo, 'siap deploy'), 'TODO.md konsisten');
    check(str_contains($prd, $slug) || str_contains($prd, '#33'), 'PRD.md konsisten');
    check(str_contains($roadmap, $slug), 'Roadmap konsisten');
    check(str_contains($roadmap, '#35'), 'Roadmap teaser #35');
}

// Patch #8 idempotent
$patch = file_get_contents(__DIR__ . '/../database/seeders/PatchArticle8ServoSeeder.php');
check(str_contains($patch, 'str_contains($article->body'), 'Patch #8 idempotent guard');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
