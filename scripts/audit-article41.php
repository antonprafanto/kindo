<?php

/**
 * Audit artikel #41 — Class dan Object Pertama (Seri 3).
 * Usage: php scripts/audit-article41.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article41Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug = 'class-dan-object-pertama-python';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article41Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #41 — Class & Object ===\n\n";

if (! $checkProduction) {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TagSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article41Seeder', '--force' => true]);
}

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'programming', 'Kategori programming');
check($article?->is_featured === false, 'is_featured false (bukan pembuka)');

$requiredTags = ['python', 'oop', 'oop-class'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 Mengenal OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '(#40)'), 'Anchor ber-nomor (#40)');
check(str_contains($body, '#41 (ini)'), 'Self-ref #41 (ini)');
check(str_contains($body, 'class Buku'), 'Contoh class Buku');
check(str_contains($body, '__init__'), 'Menyebut __init__');
check(str_contains($body, 'def info(self)'), 'Method info(self)');
check(str_contains($body, 'id(') || str_contains($body, 'id(buku'), 'Menyebut id()');
check(str_contains($body, ' is '), 'Menyebut operator is');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0') || str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar teks gelap dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada section Kode lengkap');
check(str_contains($body, 'python buku.py') || str_contains($body, 'buku.py'), 'Instruksi jalankan buku.py');
check(str_contains($body, 'missing') && str_contains($body, 'required positional argument'), 'Kesalahan umum: TypeError argumen');
check(str_contains($body, 'buku_a == buku_b') && str_contains($body, 'False'), 'Klarifikasi default == untuk class');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan singkat');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');
check(! preg_match('/(?<![\w\/"#>])#(?:4[3-9]|[5-9]\d)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak ada plain #43+ di luar link');
check(str_contains($body, 'attribute-method-constructor-init-python'), 'Forward link ke #42');
check(str_contains($body, '(#42)'), 'Anchor ber-nomor (#42)');
check(! str_contains($body, '/artikel/encapsulation-property-python-oop'), 'Tidak hardlink slug #43');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-41'), 'Route publish-article-41');
check(str_contains($yml, 'publish-article-41'), 'CI workflow publish-article-41');
check(str_contains($deploy, 'publishArticle41'), 'DeployController publishArticle41');
check(str_contains($deploy, $slug), 'DeployController cek slug #41');

$css = file_get_contents(__DIR__.'/../resources/css/app.css');
check(str_contains($css, 'figure[style*="F5F5F0"]') || str_contains($css, "figure[style*=\"F5F5F0\"]"), 'CSS sumber: rule figure F5F5F0 (dark-mode, outside layer)');
check(str_contains($css, 'html.dark .article-body figure[style*="F5F5F0"]'), 'CSS sumber: html.dark figure override');

check(str_contains($css, 'list-style:none'), 'CSS sumber: reset ol list-style:none di figure');

if ($checkProduction) {
    echo "\n=== Pass production HTTP ===\n";
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'ignore_errors' => true]]);
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug, false, $ctx);
    check(is_string($html) && str_contains($html, 'Class dan Object'), 'Prod page berisi judul');
    check(is_string($html) && str_contains($html, '<svg'), 'Prod page berisi SVG');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
