<?php

/**
 * Audit artikel #45 — Polymorphism (Seri 3).
 * Usage: php scripts/audit-article45.php [--production]
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article45Seeder;

$passed = 0;
$failed = 0;
$production = in_array('--production', $argv ?? [], true);

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'polymorphism-python-oop';

echo "=== Audit Artikel #45 — Polymorphism ===\n\n";

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
foreach (['python', 'oop', 'polymorphism'] as $t) {
    check(in_array($t, $tags, true), "Tag: {$t}");
}

$requiredLinks = [
    'inheritance-pewarisan-class-python' => 'Artikel #44 Inheritance',
    'encapsulation-property-python-oop' => 'Artikel #43 Encapsulation',
    'attribute-method-constructor-init-python' => 'Artikel #42 Attribute/__init__',
    'class-dan-object-pertama-python' => 'Artikel #41 Class & Object',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 Mengenal OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '(#44)'), 'Anchor (#44)');
check(str_contains($body, '#45 (ini)'), 'Self-ref #45 (ini)');
check(str_contains($body, 'for item in koleksi'), 'Loop koleksi polimorfik');
check(str_contains($body, 'duck typing') || str_contains($body, 'Duck typing'), 'Bahas duck typing');
check(str_contains($body, 'KatalogEntry'), 'Contoh duck typing KatalogEntry');
check(str_contains($body, 'tipe object yang sebenarnya'), 'Method lookup tipe aktual');
check(str_contains($body, 'cek tipe anak'), 'Jebakan urutan isinstance');
check(str_contains($body, 'AttributeError') && str_contains($body, 'dict'), 'Demo AttributeError dict');
check(str_contains($body, 'isinstance(ebook, Buku)'), 'FAQ isinstance ebook→Buku');
check(str_contains($body, 'cetak_salah') || str_contains($body, '# SALAH'), 'Demo SALAH isinstance');
check(str_contains($body, 'unduh'), 'isinstance untuk aksi khusus unduh');
check(str_contains($body, 'isinstance'), 'Bahas isinstance');
check(str_contains($body, 'oop45Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
$loopPos = strpos($body, 'Satu loop untuk semua');
$svgPos = strpos($body, 'oop45Arrow');
check($loopPos !== false && $svgPos !== false && $svgPos > $loopPos, 'SVG setelah section loop');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'koleksi_polimorfik.py'), 'Instruksi file koleksi_polimorfik.py');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'Abstraction') || str_contains($body, 'ABC'), 'Teaser Abstraction/ABC');
check(str_contains($body, '6/10 artikel live'), 'Progress 6/10 live');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[6-9]|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #46+ di luar link');
check(! preg_match('/#45(?!\s*\(ini\))/', $plain), 'Tidak ada plain #45 selain #45 (ini)');
check(! str_contains($body, '/artikel/abstraction-abc-python-oop'), 'Tidak hardlink slug #46');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-45'), 'Route publish-article-45');
check(str_contains($yml, 'publish-article-45'), 'CI workflow publish-article-45');
check(str_contains($deploy, 'publishArticle45'), 'DeployController publishArticle45');
check(str_contains($deploy, $slug), 'DeployController cek slug #45');

// Backlink #44→#45 wajib sebelum deploy #45
$a44 = file_get_contents(__DIR__.'/../database/seeders/Article44Seeder.php');
check(str_contains($a44, 'polymorphism-python-oop'), 'Backlink #44→#45 di Article44Seeder');
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
check(str_contains($body, 'cabang Buku'), 'Demo jebakan urutan isinstance');
check(str_contains($a40, 'polymorphism-python-oop') && str_contains($a40, 'encapsulation-property-python-oop'), 'Indeks #40 pilar Encapsulation+#45');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'Polymorphism'), 'Prod page berisi judul');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
