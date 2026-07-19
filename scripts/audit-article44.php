<?php

/**
 * Audit artikel #44 — Inheritance + super() (Seri 3).
 * Usage: php scripts/audit-article44.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article44Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug = 'inheritance-pewarisan-class-python';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article44Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #44 — Inheritance / super() ===\n\n";

if (! $checkProduction) {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TagSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article44Seeder', '--force' => true]);
}

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'programming', 'Kategori programming');
check($article?->is_featured === false, 'is_featured false');

$requiredTags = ['python', 'oop', 'inheritance'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'encapsulation-property-python-oop' => 'Artikel #43 Encapsulation',
    'attribute-method-constructor-init-python' => 'Artikel #42 Attribute/__init__',
    'class-dan-object-pertama-python' => 'Artikel #41 Class & Object',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 Mengenal OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '(#43)'), 'Anchor (#43)');
check(str_contains($body, '(#42)'), 'Anchor (#42)');
check(str_contains($body, '#44 (ini)'), 'Self-ref #44 (ini)');
check(str_contains($body, 'class Ebook(Buku)'), 'class Ebook(Buku)');
check(str_contains($body, 'super().__init__'), 'super().__init__');
check(str_contains($body, 'def info(self)'), 'Override / method info');
check(str_contains($body, 'EbookSalah') || str_contains($body, '# SALAH'), 'Demo SALAH lupa super');
check(str_contains($body, 'AttributeError'), 'Bahas AttributeError');
check(str_contains($body, 'isinstance'), 'Bahas isinstance');
check(str_contains($body, 'Audiobook'), 'Audiobook selaras SVG/latihan');
check(str_contains($body, '@property'), 'Klarifikasi kaitan @property/#43');
check(str_contains($body, 'tetap object'), 'Jelaskan self di method anak');
$overridePos = strpos($body, 'Override method info()');
$svgPos = strpos($body, 'oop44Arrow');
check($overridePos !== false && $svgPos !== false && $svgPos > $overridePos, 'SVG setelah override');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'oop44Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_ebook.py'), 'Instruksi file perpustakaan_ebook.py');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'Polymorphism'), 'Teaser Polymorphism');
check(str_contains($body, '/artikel/polymorphism-python-oop'), 'Hardlink slug #45');
check(str_contains($body, '10/10 artikel live'), 'Progress 10/10 live');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[6-9]|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #46+ di luar link');
check(! preg_match('/#44(?!\s*\(ini\))/', $plain), 'Tidak ada plain #44 selain #44 (ini)');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-44'), 'Route publish-article-44');
check(str_contains($yml, 'publish-article-44'), 'CI workflow publish-article-44');
check(str_contains($deploy, 'publishArticle44'), 'DeployController publishArticle44');
check(str_contains($deploy, $slug), 'DeployController cek slug #44');

$a43 = file_get_contents(__DIR__.'/../database/seeders/Article43Seeder.php');
check(str_contains($a43, $slug), 'Backlink #43→#44 di Article43Seeder');

$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
check(str_contains($a40, $slug), 'Forward link #40 indeks → #44');

if ($checkProduction) {
    echo "\n=== Pass production HTTP ===\n";
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'ignore_errors' => true]]);
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug, false, $ctx);
    check(is_string($html) && str_contains($html, 'Inheritance'), 'Prod page berisi judul');
    check(is_string($html) && str_contains($html, '<svg'), 'Prod page berisi SVG');
    check(is_string($html) && str_contains($html, 'color:#1a1a1a'), 'Prod Pola Dasar #1a1a1a');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
