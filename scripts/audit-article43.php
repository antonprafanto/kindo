<?php

/**
 * Audit artikel #43 — Encapsulation + @property (Seri 3).
 * Usage: php scripts/audit-article43.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article43Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug = 'encapsulation-property-python-oop';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article43Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #43 — Encapsulation / @property ===\n\n";

if (! $checkProduction) {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TagSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article43Seeder', '--force' => true]);
}

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'programming', 'Kategori programming');
check($article?->is_featured === false, 'is_featured false');

$requiredTags = ['python', 'oop', 'encapsulation'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'attribute-method-constructor-init-python' => 'Artikel #42 Attribute/__init__',
    'class-dan-object-pertama-python' => 'Artikel #41 Class & Object',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 Mengenal OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '(#42)'), 'Anchor (#42)');
check(str_contains($body, '(#41)'), 'Anchor (#41)');
check(str_contains($body, '(#40)'), 'Anchor (#40)');
check(str_contains($body, '#43 (ini)'), 'Self-ref #43 (ini)');
check(str_contains($body, '@property'), 'Menyebut @property');
check(str_contains($body, '@stok.setter') || str_contains($body, '@tahun.setter'), 'Ada @setter');
check(str_contains($body, 'self._stok'), 'Attribute internal _stok');
check(str_contains($body, 'name-mangling') || str_contains($body, '_Buku__'), 'Bahas name-mangling');
check(str_contains($body, 'RecursionError'), 'Bahas RecursionError property');
check(str_contains($body, 'BukuSalah') && str_contains($body, 'BukuBenar'), 'Demo RecursionError SALAH/BENAR');
check(str_contains($body, '5/10 artikel live'), 'Progress 5/10 live');
check(str_contains($body, 'def pinjam(self)'), 'Method pinjam(self)');
check(str_contains($body, 'self.stok -= 1') && (str_contains($body, 'lewat property') || str_contains($body, 'juga lewat property')), 'Jelaskan self.stok -= 1 lewat property');
check(strpos($body, '@setter — validasi') < strpos($body, 'oop43Arrow'), 'Diagram SVG setelah section @setter');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'oop43Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'buku_terlindungi.py'), 'Instruksi file buku_terlindungi.py');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Seri 3'), 'Menyebut Seri 3');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'Inheritance'), 'Teaser Inheritance');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[4-9]|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #44+ di luar link');
check(! preg_match('/#43(?!\s*\(ini\))/', $plain), 'Tidak ada plain #43 selain #43 (ini)');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Hardlink #43→#44');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-43'), 'Route publish-article-43');
check(str_contains($yml, 'publish-article-43'), 'CI workflow publish-article-43');
check(str_contains($deploy, 'publishArticle43'), 'DeployController publishArticle43');
check(str_contains($deploy, $slug), 'DeployController cek slug #43');

$a42 = file_get_contents(__DIR__.'/../database/seeders/Article42Seeder.php');
check(str_contains($a42, $slug), 'Backlink #42→#43 di Article42Seeder');

$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
check(str_contains($a40, $slug), 'Forward link #40 indeks → #43');

if ($checkProduction) {
    echo "\n=== Pass production HTTP ===\n";
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'ignore_errors' => true]]);
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug, false, $ctx);
    check(is_string($html) && str_contains($html, 'Encapsulation'), 'Prod page berisi judul');
    check(is_string($html) && str_contains($html, '<svg'), 'Prod page berisi SVG');
    check(is_string($html) && str_contains($html, 'color:#1a1a1a'), 'Prod Pola Dasar #1a1a1a');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
