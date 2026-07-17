<?php

/**
 * Audit artikel #40 — Mengenal OOP (Seri 3 pembuka).
 * Usage: php scripts/audit-article40.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article40Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug = 'mengenal-oop-cara-berpikir-dengan-objek-python';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article40Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #40 — Seri 3 OOP ===\n\n";

if (! $checkProduction) {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TagSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article40Seeder', '--force' => true]);
}

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'programming', 'Kategori programming');
check($article?->is_featured === true, 'is_featured true (pembuka seri)');

$requiredTags = ['python', 'oop', 'oop-class'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt' => 'Artikel #39 Capstone Seri 2',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32' => 'Artikel #18 Python MQTT',
    'class-dan-object-pertama-python' => 'Artikel #41 Class & Object',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, 'python --version') || str_contains($body, 'Python 3'), 'Soft-landing Python / versi');
check(str_contains($body, 'class Buku'), 'Contoh class Buku');
check(str_contains($body, '__init__'), 'Menyebut __init__');
check(str_contains($body, 'Encapsulation'), 'Preview Encapsulation');
check(str_contains($body, 'Inheritance'), 'Preview Inheritance');
check(str_contains($body, 'Polymorphism'), 'Preview Polymorphism');
check(str_contains($body, 'Abstraction'), 'Preview Abstraction');
check(str_contains($body, '<svg'), 'Diagram SVG class vs object');
check(str_contains($body, 'background:#F5F5F0') || str_contains($body, 'background:#F5F5F0'), 'Figure dark-mode bg #F5F5F0');
check(str_contains($body, 'Pola Dasar') || str_contains($body, 'berpikir objek'), 'Pola Dasar / berpikir objek');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, '#40 (ini)'), 'Self-ref #40 (ini)');
check(str_contains($body, 'language-python'), 'Blok kode language-python');
check(str_contains($body, 'class-dan-object-pertama-python'), 'Forward link ke #41');
check(str_contains($body, '(#41)'), 'Anchor ber-nomor (#41)');
check(! preg_match('/(?<![\w\/"#>])#(?:4[2-9]|[5-9]\d)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak ada plain #42+ di luar link');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(str_contains($body, '(#39)') && str_contains($body, '(#18)'), 'Anchor ber-nomor (#18)/(#39)');
check(str_contains($body, 'oop40ArrowOrange') || str_contains($body, 'marker-end'), 'SVG marker panah');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-40'), 'Route publish-article-40');
check(str_contains($yml, 'publish-article-40'), 'CI workflow publish-article-40');
check(str_contains($deploy, 'publishArticle40'), 'DeployController publishArticle40');
check(str_contains($deploy, $slug), 'DeployController cek slug #40');

if ($checkProduction) {
    echo "\n=== Pass production HTTP ===\n";
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'ignore_errors' => true]]);
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug, false, $ctx);
    check(is_string($html) && str_contains($html, 'Mengenal OOP'), 'Prod page berisi judul');
    check(is_string($html) && str_contains($html, '<svg'), 'Prod page berisi SVG');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
