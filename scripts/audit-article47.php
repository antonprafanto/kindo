<?php

/**
 * Audit artikel #47 — Composition vs Inheritance (Seri 3).
 * Usage: php scripts/audit-article47.php [--production]
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

$slug = 'composition-vs-inheritance-python';

echo "=== Audit Artikel #47 — Composition vs Inheritance ===\n\n";

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
foreach (['python', 'oop', 'inheritance', 'composition'] as $t) {
    check(in_array($t, $tags, true), "Tag: {$t}");
}

$requiredLinks = [
    'abstraction-abc-python-oop' => 'Artikel #46 Abstraction',
    'inheritance-pewarisan-class-python' => 'Artikel #44 Inheritance',
    'polymorphism-python-oop' => 'Artikel #45 Polymorphism',
    'encapsulation-property-python-oop' => 'Artikel #43 Encapsulation',
    'attribute-method-constructor-init-python' => 'Artikel #42 Attribute',
    'class-dan-object-pertama-python' => 'Artikel #41 Class',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '#47 (ini)'), 'Self-ref #47 (ini)');
check(str_contains($body, 'PerpustakaanSalah'), 'Anti-pola PerpustakaanSalah');
check(str_contains($body, 'PerpustakaanBenar') || str_contains($body, 'class Perpustakaan'), 'Composition Perpustakaan');
check(str_contains($body, 'self.koleksi'), 'Atribut koleksi composition');
check(str_contains($body, 'has-a') || str_contains($body, 'punya'), 'Bahas has-a / punya');
check(str_contains($body, 'is-a') || str_contains($body, 'adalah'), 'Bahas is-a');
check(str_contains($body, 'oop47Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_komposisi.py'), 'Instruksi file contoh');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'dataclass') || str_contains($body, 'Special Methods'), 'Teaser #48');
check(str_contains($body, '10/10 artikel live'), 'Progress 10/10 live');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:49|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #49+ di luar link');
check(! preg_match('/#47(?!\s*\(ini\))/', $plain), 'Tidak ada plain #47 selain #47 (ini)');
check(str_contains($body, '/artikel/special-methods-dataclass-python'), 'Backlink live ke #48');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-47'), 'Route publish-article-47');
check(str_contains($yml, 'publish-article-47'), 'CI workflow publish-article-47');
check(str_contains($deploy, 'publishArticle47'), 'DeployController publishArticle47');
check(str_contains($deploy, $slug), 'DeployController cek slug #47');

check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article46Seeder.php'), 'composition-vs-inheritance-python'), 'Backlink #46→#47');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'composition-vs-inheritance-python'), 'Indeks #40→#47');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'Composition'), 'Prod page berisi judul');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
