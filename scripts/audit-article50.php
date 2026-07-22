<?php

/**
 * Audit artikel #50 — Factory & Strategy (Tier 2).
 * Usage: php scripts/audit-article50.php [--production]
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

$slug = 'design-pattern-factory-strategy-python';

echo "=== Audit Artikel #50 — Factory & Strategy ===\n\n";

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
foreach (['python', 'oop', 'polymorphism', 'composition', 'design-pattern'] as $t) {
    check(in_array($t, $tags, true), "Tag: {$t}");
}

$requiredLinks = [
    'capstone-sistem-perpustakaan-mini-oop-python' => 'Artikel #49 Capstone',
    'polymorphism-python-oop' => 'Artikel #45 Polymorphism',
    'abstraction-abc-python-oop' => 'Artikel #46 Abstraction',
    'composition-vs-inheritance-python' => 'Artikel #47 Composition',
    'special-methods-dataclass-python' => 'Artikel #48 Special Methods',
    'encapsulation-property-python-oop' => 'Artikel #43 Encapsulation',
    'inheritance-pewarisan-class-python' => 'Artikel #44 Inheritance',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => 'Artikel #40 OOP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Link internal: {$label}");
}

check(str_contains($body, '#50 (ini)'), 'Self-ref #50 (ini)');
check(str_contains($body, 'buat_item'), 'Bahas Factory buat_item');
check(str_contains($body, 'DendaFlat') && str_contains($body, 'DendaPerHari'), 'Bahas Strategy denda');
check(str_contains($body, 'StrategiDenda'), 'Bahas ABC StrategiDenda');
check(str_contains($body, 'oop50Arrow') || str_contains($body, 'marker-end'), 'SVG marker');
check(str_contains($body, '<svg'), 'Diagram SVG');
check(str_contains($body, 'aria-label'), 'Figure aria-label');
check(str_contains($body, 'figcaption'), 'Figcaption');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-mode safe');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'factory_strategy_perpustakaan.py'), 'Instruksi file contoh');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(str_contains($body, 'language-python'), 'Blok language-python');
check(str_contains($body, 'MicroPython') || str_contains($body, 'Flask'), 'Teaser #51/#52 tanpa hardlink wajib');
check(substr_count($body, '<h2') >= 8, 'Minimal 8 H2');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[1-9]|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #51+ di luar link');
check(! preg_match('/#50(?!\s*\(ini\))/', $plain), 'Tidak ada plain #50 selain #50 (ini)');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-50'), 'Route publish-article-50');
check(str_contains($yml, 'publish-article-50'), 'CI workflow publish-article-50');
check(str_contains($deploy, 'publishArticle50'), 'DeployController publishArticle50');
check(str_contains($deploy, $slug), 'DeployController cek slug #50');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php'), $slug), 'Backlink #49→#50 di seeder');
check(str_contains($deploy, 'Article49Seeder') && str_contains($deploy, 'publishArticle50'), 'Hook #50 bundling reseed #49');

if ($production) {
    $html = @file_get_contents('https://kodingindonesia.com/artikel/'.$slug);
    check(is_string($html) && str_contains($html, 'Factory'), 'Prod page berisi judul');
}

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
