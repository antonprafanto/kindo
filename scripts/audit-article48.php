<?php

/**
 * Audit artikel #48 — Special Methods & Dataclass (Seri 3).
 * Usage: php scripts/audit-article48.php [--production]
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;

$passed = 0;
$failed = 0;
$production = in_array('--production', $argv ?? [], true);

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'special-methods-dataclass-python';

echo "=== Audit Artikel #48 — Special Methods & Dataclass ===\n\n";

if ($production) {
    $article = Article::published()->where('slug', $slug)->first();
} else {
    $article = Article::where('slug', $slug)->first();
}

check($article !== null, 'Artikel ada di database');
if (! $article) {
    echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
    exit(1);
}

$body = (string) $article->body;

check($article->status === 'published', 'Status published');
check($article->published_at !== null, 'published_at terisi');
check(optional($article->category)->slug === 'programming', 'Kategori programming');
check($article->is_featured === false, 'is_featured false');

$tags = $article->tags->pluck('slug')->all();
foreach (['python', 'oop', 'dataclass'] as $t) {
    check(in_array($t, $tags, true), "Tag: {$t}");
}

$requiredLinks = [
    'composition-vs-inheritance-python' => 'Artikel #47 Composition',
    'attribute-method-constructor-init-python' => 'Artikel #42 Attribute',
    'encapsulation-property-python-oop' => 'Artikel #43 Encapsulation',
    'abstraction-abc-python-oop' => 'Artikel #46 Abstraction',
    'polymorphism-python-oop' => 'Artikel #45 Polymorphism',
    'inheritance-pewarisan-class-python' => 'Artikel #44 Inheritance',
    'class-dan-object-pertama-python' => 'Artikel #41 Class',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '#48 (ini)'), 'Self-ref #48 (ini)');
check(str_contains($body, '__str__'), 'Bahas __str__');
check(str_contains($body, '__repr__'), 'Bahas __repr__');
check(str_contains($body, '__eq__'), 'Bahas __eq__');
check(str_contains($body, '@dataclass') || str_contains($body, 'dataclass'), 'Bahas dataclass');
check(str_contains($body, 'oop48Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'buku_special_methods.py'), 'Instruksi file contoh');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'Capstone') || str_contains($body, 'Perpustakaan Mini'), 'Teaser #49');
check(str_contains($body, '10/10 artikel live'), 'Progress 10/10 live');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[0-9]|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #50+ di luar link');
check(! preg_match('/#48(?!\s*\(ini\))/', $plain), 'Tidak ada plain #48 selain #48 (ini)');
check(str_contains($body, '/artikel/capstone-sistem-perpustakaan-mini-oop-python'), 'Hardlink slug #49');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-48'), 'Route publish-article-48');
check(str_contains($yml, 'publish-article-48'), 'CI workflow publish-article-48');
check(str_contains($deploy, 'publishArticle48'), 'DeployController publishArticle48');
check(str_contains($deploy, $slug), 'DeployController cek slug #48');

check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article47Seeder.php'), 'special-methods-dataclass-python'), 'Backlink #47→#48');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'special-methods-dataclass-python'), 'Indeks #40→#48');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'Special Methods'), 'Prod page berisi judul');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
