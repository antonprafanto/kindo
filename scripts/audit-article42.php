<?php

/**
 * Audit artikel #42 — Attribute, Method, __init__ (Seri 3).
 * Usage: php scripts/audit-article42.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article42Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug = 'attribute-method-constructor-init-python';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article42Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #42 — Attribute / Method / __init__ ===\n\n";

if (! $checkProduction) {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TagSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article42Seeder', '--force' => true]);
}

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'programming', 'Kategori programming');
check($article?->is_featured === false, 'is_featured false');

$requiredTags = ['python', 'oop', 'oop-class'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'class-dan-object-pertama-python' => 'Artikel #41 Class & Object',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 Mengenal OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '(#41)'), 'Anchor (#41)');
check(str_contains($body, '(#40)'), 'Anchor (#40)');
check(str_contains($body, '#42 (ini)'), 'Self-ref #42 (ini)');
check(str_contains($body, '__init__'), 'Menyebut __init__');
check(str_contains($body, 'def pinjam(self)'), 'Method pinjam(self)');
check(str_contains($body, 'def info(self)'), 'Method info(self)');
check(str_contains($body, 'def pinjam_untuk(self'), 'Method pinjam_untuk lengkap dalam class');
check(str_contains($body, 'self.stok'), 'Attribute self.stok');
check(! str_contains($body, 'dipinjam'), 'Tidak ada attribute dipinjam yang ditinggalkan');
check(str_contains($body, 'BukuSalah') && str_contains($body, 'BukuBenar'), 'Demo kode jebakan list class bersama');
check(str_contains($body, 'tahun tidak masuk akal') && substr_count($body, 'if tahun') >= 2, 'Validasi tahun juga di Kode lengkap');
check(str_contains($body, '4/10 artikel live'), 'Progress Seri 3 mencerminkan 4/10 live');
check(str_contains($body, 'lupa') || str_contains($body, 'missing 1 required positional argument: \'self\''), 'Bahas error lupa self');
check(str_contains($body, 'Buku.__init__() missing') || str_contains($body, 'missing ... required positional argument'), 'Kesalahan umum: TypeError argumen __init__');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'oop42Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_mini.py'), 'Instruksi file perpustakaan_mini.py');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');
$plain42 = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[4-9]|[5-9]\d)(?!\s*\(ini\))/', $plain42), 'Tidak ada plain #44+ di luar link');
check(! preg_match('/#42(?!\s*\(ini\))/', $plain42), 'Tidak ada plain #42 selain #42 (ini)');
check(str_contains($body, '/artikel/encapsulation-property-python-oop'), 'Forward link ke #43 Encapsulation');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-42'), 'Route publish-article-42');
check(str_contains($yml, 'publish-article-42'), 'CI workflow publish-article-42');
check(str_contains($deploy, 'publishArticle42'), 'DeployController publishArticle42');
check(str_contains($deploy, $slug), 'DeployController cek slug #42');

$a41 = file_get_contents(__DIR__.'/../database/seeders/Article41Seeder.php');
check(str_contains($a41, $slug), 'Backlink #41→#42 di Article41Seeder');

$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
check(str_contains($a40, $slug), 'Forward link #40 indeks → #42');

if ($checkProduction) {
    echo "\n=== Pass production HTTP ===\n";
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'ignore_errors' => true]]);
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug, false, $ctx);
    check(is_string($html) && str_contains($html, 'Attribute, Method'), 'Prod page berisi judul');
    check(is_string($html) && str_contains($html, '<svg'), 'Prod page berisi SVG');
    check(is_string($html) && str_contains($html, 'color:#1a1a1a'), 'Prod Pola Dasar #1a1a1a');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
