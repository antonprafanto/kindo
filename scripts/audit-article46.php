<?php

/**
 * Audit artikel #46 — Abstraction & ABC (Seri 3).
 * Usage: php scripts/audit-article46.php [--production]
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

$slug = 'abstraction-abc-python-oop';

echo "=== Audit Artikel #46 — Abstraction & ABC ===\n\n";

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
foreach (['python', 'oop', 'abstraction'] as $t) {
    check(in_array($t, $tags, true), "Tag: {$t}");
}

$requiredLinks = [
    'polymorphism-python-oop' => 'Artikel #45 Polymorphism',
    'inheritance-pewarisan-class-python' => 'Artikel #44 Inheritance',
    'encapsulation-property-python-oop' => 'Artikel #43 Encapsulation',
    'attribute-method-constructor-init-python' => 'Artikel #42 Attribute/__init__',
    'class-dan-object-pertama-python' => 'Artikel #41 Class & Object',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 Mengenal OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '(#45)'), 'Anchor (#45)');
check(str_contains($body, '#46 (ini)'), 'Self-ref #46 (ini)');
check(str_contains($body, 'from abc import'), 'Import abc');
check(str_contains($body, '@abstractmethod') || str_contains($body, 'abstractmethod'), 'Bahas abstractmethod');
check(str_contains($body, 'class Pinjaman'), 'Kontrak Pinjaman');
check(str_contains($body, 'BukuFisik') && str_contains($body, 'EbookLisensi'), 'Dua implementasi konkret');
check(str_contains($body, 'BukuBelumSiap') || str_contains($body, "Can't instantiate"), 'Demo subclass belum lengkap');
check(str_contains($body, 'TypeError'), 'Bahas TypeError abstract');
check(str_contains($body, 'isinstance(item, Pinjaman)'), 'isinstance cek kontrak');
check(str_contains($body, 'EntriDuck'), 'Demo duck typing vs ABC');
check(str_contains($body, 'def label'), 'Method konkret di ABC');
check(str_contains($body, 'oop46Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
$abcPos = strpos($body, 'Isi kontrak');
$svgPos = strpos($body, 'oop46Arrow');
check($abcPos !== false && $svgPos !== false && $svgPos > $abcPos, 'SVG setelah section implementasi');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'kontrak_pinjaman.py'), 'Instruksi file kontrak_pinjaman.py');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'Composition'), 'Teaser Composition');
check(str_contains($body, '7/10 artikel live'), 'Progress 7/10 live');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[7-9]|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #47+ di luar link');
check(! preg_match('/#46(?!\s*\(ini\))/', $plain), 'Tidak ada plain #46 selain #46 (ini)');
check(! str_contains($body, '/artikel/composition-vs-inheritance-python'), 'Tidak hardlink slug #47');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-46'), 'Route publish-article-46');
check(str_contains($yml, 'publish-article-46'), 'CI workflow publish-article-46');
check(str_contains($deploy, 'publishArticle46'), 'DeployController publishArticle46');
check(str_contains($deploy, $slug), 'DeployController cek slug #46');

$a45 = file_get_contents(__DIR__.'/../database/seeders/Article45Seeder.php');
check(str_contains($a45, 'abstraction-abc-python-oop'), 'Backlink #45→#46 di Article45Seeder');
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
check(str_contains($a40, 'abstraction-abc-python-oop'), 'Indeks #40→#46');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'Abstraction'), 'Prod page berisi judul');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
