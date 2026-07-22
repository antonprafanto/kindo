<?php

/**
 * Audit artikel #49 — Capstone Perpustakaan Mini OOP (Seri 3).
 * Usage: php scripts/audit-article49.php [--production]
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

$slug = 'capstone-sistem-perpustakaan-mini-oop-python';

echo "=== Audit Artikel #49 — Capstone Perpustakaan Mini OOP ===\n\n";

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
check($article->is_featured === true, 'is_featured true');

$tags = $article->tags->pluck('slug')->all();
foreach (['python', 'oop', 'composition', 'dataclass'] as $t) {
    check(in_array($t, $tags, true), "Tag: {$t}");
}

$requiredLinks = [
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 OOP',
    'class-dan-object-pertama-python' => 'Artikel #41 Class',
    'attribute-method-constructor-init-python' => 'Artikel #42 Attribute',
    'encapsulation-property-python-oop' => 'Artikel #43 Encapsulation',
    'inheritance-pewarisan-class-python' => 'Artikel #44 Inheritance',
    'polymorphism-python-oop' => 'Artikel #45 Polymorphism',
    'abstraction-abc-python-oop' => 'Artikel #46 Abstraction',
    'composition-vs-inheritance-python' => 'Artikel #47 Composition',
    'special-methods-dataclass-python' => 'Artikel #48 Special Methods',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '#49 (ini)'), 'Self-ref #49 (ini)');
check(str_contains($body, 'oop49Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_mini.py'), 'Instruksi file contoh');
check(str_contains($body, 'Latihan singkat') || str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Indeks Seri 3'), 'Ada Indeks Seri 3');
check(str_contains($body, '10/10 artikel live'), 'Progress 10/10 live');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'dataclass'), 'Bahas dataclass');
check(str_contains($body, '__str__'), 'Jembatan special method __str__');
check(str_contains($body, '__repr__') && str_contains($body, '__eq__'), 'Sebut __repr__/__eq__ dari dataclass');
check(str_contains($body, 'class Perpustakaan'), 'Ada class Perpustakaan');
check(str_contains($body, 'demo('), 'Ada demo()');
check(str_contains($body, 'kembalikan'), 'Demo/fitur kembalikan');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Latihan taut #44');
check(! str_contains($body, 'dataclass, field'), 'Tidak ada import field tak terpakai');
check(! preg_match('/<pre><code class="language-python">[\s\S]*?\binput\(/', $body), 'Tidak ada input( di blok python');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#49(?!\s*\(ini\))/', $plain), 'Tidak ada plain #49 selain #49 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5\d|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #50+ di luar link');
check(str_contains($body, '/artikel/design-pattern-factory-strategy-python'), 'Hardlink Tier 2 #50 Factory');
check(str_contains($body, '/artikel/oop-micropython-esp32-class-sensor'), 'Hardlink Tier 2 #51 MicroPython');
check(! preg_match('/\/artikel\/[^"\'>\s]*(?:tier-2|artikel-5[2-9]|flask|fastapi|seri-4)/', $body), 'Tidak hardlink slug #52+ unpublished');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-49'), 'Route publish-article-49');
check(str_contains($yml, 'publish-article-49'), 'CI workflow publish-article-49');
check(str_contains($deploy, 'publishArticle49'), 'DeployController publishArticle49');
check(str_contains($deploy, $slug), 'DeployController cek slug #49');
check(str_contains($deploy, 'Article48Seeder') && str_contains($deploy, 'Article40Seeder'), 'Hook #49 reseed backlink #48+#40');
check(str_contains($deploy, 'Article 49 backlink #48 incomplete') || str_contains($deploy, 'backlink #48'), 'Hook #49 verifikasi body backlink #48');
check(str_contains($deploy, 'Article 49 backlink #40 incomplete') || str_contains($deploy, 'backlink #40'), 'Hook #49 verifikasi body backlink #40');

check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article48Seeder.php'), 'capstone-sistem-perpustakaan-mini-oop-python'), 'Backlink #48→#49');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'capstone-sistem-perpustakaan-mini-oop-python'), 'Indeks #40→#49');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'Perpustakaan Mini'), 'Prod page berisi judul');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
