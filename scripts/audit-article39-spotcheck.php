<?php

/** Spot-check + paranoid audit #39 — supplemental checks. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article39Seeder;
use Illuminate\Support\Facades\Artisan;

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

echo "=== SPOTCHECK + PARANOID #39 ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article39Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle38GreenhouseSeeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
check($article?->status === 'published', 'Artikel visible setelah seed chain');
check($article?->tags()->count() === 6, 'Tag count = 6');
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

preg_match_all('#href="/artikel/([^"]+)"#', $body, $links);
foreach (array_unique($links[1]) as $target) {
    if ($target === $slug) {
        check(false, "Self-link ditemukan: {$target}");
        continue;
    }
    $a = Article::where('slug', $target)->first();
    check($a && $a->status === 'published', "Target published: {$target}");
}

$h2 = substr_count($body, '<h2>');
$words = str_word_count(strip_tags($body));
check($h2 >= 20, "H2 count ≥20 ({$h2})");
check($words >= 1500, "Word count ≥1500 ({$words})");
check(str_contains($body, 'esp_deep_sleep'), 'Deep sleep API');
check(str_contains($body, 'mqtt.subscribe'), 'mqtt.subscribe di sketch aktuator');
check(str_contains($body, 'AUTO'), 'Mode AUTO pompa');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');
check(str_contains($body, 'kindo_esp32'), 'MQTT user kindo_esp32');
check(! preg_match('/password\s*=\s*["\'][^G]/i', strip_tags($body)), 'Tidak ada password hardcode');

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
check(substr_count($a10body, '<li><strong><a href="/artikel/') === 29, '#10 indeks 29 link');
check(str_contains($a10body, $slug), '#10 link ke #39');
check(! str_contains($a10body, 'Masih akan datang'), '#10 tidak ada teaser lama');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle39\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m39);
$h39 = $m39[1] ?? '';
foreach (['Article39Seeder', 'Article10Seeder', 'PatchArticle38GreenhouseSeeder', 'SitemapService', 'view:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h39, $needle), "Hook #39 punya {$needle}");
}

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200 lokal');
check(str_contains($html, 'Greenhouse') && str_contains($html, 'kelembaban_tanah'), 'Konten kunci ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD di HTML');

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    $todo = file_get_contents($docs . '/TODO.md');
    check(str_contains($todo, $slug) || str_contains($todo, '#39'), 'TODO.md menyebut #39');
    check(str_contains(file_get_contents($docs . '/PRD.md'), '#39'), 'PRD.md menyebut #39');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
