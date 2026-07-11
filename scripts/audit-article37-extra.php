<?php



/** Extra supplemental checks for #37. */



require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();



use App\Models\Article;

use Database\Seeders\Article37Seeder;



$passed = 0;

$failed = 0;

$slug = 'sd-card-spi-esp32-logging-data-sensor-offline';



function check(bool $ok, string $label): void

{

    global $passed, $failed;

    echo ($ok ? '✓' : '✗') . " {$label}\n";

    $ok ? $passed++ : $failed++;

}



$ref = new ReflectionClass(Article37Seeder::class);

$m = $ref->getMethod('body');

$m->setAccessible(true);

$body = $m->invoke($ref->newInstanceWithoutConstructor());

$article = Article::where('slug', $slug)->first();



echo "=== EXTRA AUDIT #37 ===\n\n";



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

check(str_contains($body, 'MOSI'), 'Section MOSI ada');

check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic MQTT sync');

check(! preg_match('/password\s*=\s*["\'][^G]/i', $body), 'Tidak ada password hardcoded di sketch');



$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');

preg_match('/function publishArticle37\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $hook);

$hookBody = $hook[1] ?? '';

check(str_contains($hookBody, 'SitemapService'), 'Hook: SitemapService');

check(str_contains($hookBody, 'Article10Seeder'), 'Hook: re-seed Article10Seeder');

check(str_contains($hookBody, 'PatchArticle36SdCardSeeder'), 'Hook: PatchArticle36SdCardSeeder');
check(str_contains($hookBody, 'PatchArticle27SdCardSeeder'), 'Hook: PatchArticle27SdCardSeeder');



$routes = file_get_contents(__DIR__ . '/../routes/web.php');

check(str_contains($routes, "publish-article-37"), 'Route publish-article-37');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');

check(preg_match('/Publish article 37 via deploy hook \(required\)/', $yml) === 1, 'CI: required hook #37');



foreach (['scripts/audit-article37.php', 'scripts/audit-article37-spotcheck.php', 'scripts/audit-article37-manual.php', 'scripts/audit-article37-paranoid.php', 'scripts/audit-article37-gapscan.php'] as $f) {

    check(file_exists(__DIR__ . '/../' . $f), "Audit file: {$f}");

}



$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';

if (is_dir($docs)) {

    $todo = file_get_contents($docs . '/TODO.md');

    $prd = file_get_contents($docs . '/PRD.md');

    $roadmap = file_get_contents($docs . '/docs/seri-esp32-iot-lanjutan.md');

    check(str_contains($todo, $slug) || str_contains($todo, '#37'), 'TODO.md konsisten');

    check(str_contains($prd, $slug) || str_contains($prd, '#37'), 'PRD.md konsisten');

    check(str_contains($roadmap, $slug) || str_contains($roadmap, '#37'), 'Roadmap konsisten');

    check(str_contains($roadmap, '#38') || str_contains($roadmap, 'HTTPS'), 'Roadmap teaser #38');

}



$patch = file_get_contents(__DIR__ . '/../database/seeders/PatchArticle36SdCardSeeder.php');

check(str_contains($patch, 'str_contains($article->body') || str_contains($patch, 'str_contains($body'), 'Patch #36 idempotent guard');



echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";

exit($failed > 0 ? 1 : 0);

