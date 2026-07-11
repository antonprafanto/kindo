<?php

/** Extra supplemental checks for #39 — parity dengan audit #38. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article39Seeder;

$passed = 0;
$failed = 0;
$slug   = 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article39Seeder::class);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== EXTRA AUDIT #39 ===\n\n";

preg_match_all('#/artikel/([a-z0-9\-]+)#', $body, $matches);
foreach (array_unique($matches[1]) as $linkSlug) {
    if ($linkSlug === $slug) {
        continue;
    }
    $target = Article::where('slug', $linkSlug)->first();
    check($target !== null && $target->status === 'published', "Link published: {$linkSlug}");
}

check($article?->is_featured === true, 'is_featured capstone true');
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 (' . mb_strlen($article?->seo_title ?? '') . ')');
check(str_contains($body, 'BME280'), 'BME280 di konten');
check(str_contains($body, 'kelembaban_tanah'), 'JSON soil moisture');
check(! preg_match('/password\s*=\s*["\'][^G]/i', $body), 'Tidak ada password hardcoded di sketch');
check(substr_count($body, '<pre>') === substr_count($body, '</pre>'), 'Tag pre seimbang');
check(substr_count($body, '<code') <= substr_count($body, '</code>'), 'Tag code seimbang');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle39\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $hook);
$hookBody = $hook[1] ?? '';
check(str_contains($hookBody, 'SitemapService'), 'Hook: SitemapService');
check(str_contains($hookBody, 'Article10Seeder'), 'Hook: re-seed Article10Seeder');
check(str_contains($hookBody, 'PatchArticle38GreenhouseSeeder'), 'Hook: PatchArticle38GreenhouseSeeder');
check(str_contains($hookBody, 'opcache_reset'), 'Hook: opcache_reset');
check(strpos($deploy, 'function publishArticle38') < strpos($deploy, 'function publishArticle39'), 'DeployController: #38 sebelum #39');

check(str_contains(file_get_contents(__DIR__ . '/../routes/web.php'), 'publish-article-39'), 'Route publish-article-39');
check(preg_match('/Publish article 39 via deploy hook \(required\)/', file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml')) === 1, 'CI: required hook #39');

foreach (['scripts/audit-article39.php', 'scripts/audit-article39-gapscan.php', 'scripts/audit-article39-spotcheck.php', 'scripts/audit-article39-extra.php'] as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "Audit file: {$f}");
}

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    $prd = file_get_contents($docs . '/PRD.md');
    $roadmap = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, $slug) || str_contains($todo, '#39'), 'TODO.md konsisten');
    check(str_contains($prd, $slug) || str_contains($prd, '#39'), 'PRD.md konsisten');
    check(str_contains($roadmap, $slug) || str_contains($roadmap, '#39'), 'Roadmap konsisten');
    check(str_contains($roadmap, '29/29') || str_contains($roadmap, '97%'), 'Roadmap progress Seri 2');
}

$patch = file_get_contents(__DIR__ . '/../database/seeders/PatchArticle38GreenhouseSeeder.php');
check(str_contains($patch, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'Patch #38 target slug #39');
check(str_contains($patch, '$body !== $article->body'), 'Patch #38 idempotent guard');

check(str_contains($body, '29/29'), 'Penutup menyebut 29/29 selesai');
check(str_contains($body, 'GPIO 34'), 'Soil pin GPIO 34');
check(str_contains($body, 'GPIO 35'), 'LDR pin GPIO 35');
check(str_contains($body, 'GPIO 21'), 'BME280 SDA GPIO 21');
check(str_contains($body, 'analogReadResolution(12)'), 'ADC 12-bit');
check(
    ! str_contains($body, 'test.mosquitto.org')
    || str_contains($body, 'Tidak untuk data produksi'),
    'test.mosquitto hanya di FAQ peringatan (bukan rekomendasi)'
);

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
