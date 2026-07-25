<?php

/**
 * Audit utama #63 — Pagination, Filter & Pencarian (Seri 5).
 * Usage: php scripts/audit-article63.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article63Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-pagination-filter-pencarian';

echo "=== Audit Artikel #63 — Pagination Filter & Pencarian ===\n\n";

$ref = new ReflectionClass(Article63Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article63Seeder.php');

check(str_contains($body, '#63 (ini)'), 'Self-ref');
check(str_contains($body, 'paginate') && str_contains($body, 'array_slice'), 'paginate + array_slice');
check(str_contains($body, 'Filter') && str_contains($body, 'Pencarian'), 'Filter + Pencarian');
check(str_contains($body, 'laravel63pageArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_pagination_filter_pencarian_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-eloquent-relasi-peminjaman'), 'Link #62');
check(str_contains($body, '/artikel/laravel-crud-api-buku-ubah-hapus'), 'Link #61');
check(! preg_match('/(?<![\w\/"#>])#64(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #64+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#62(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #62');
check(! preg_match('/(?<![\w\/"#>])#61(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #61');
check(str_contains($body, 'Isian halaman belum rapi'), 'Gloss 422 halaman');
check(str_contains($body, 'stripos') || str_contains($body, 'cari kata'), 'Gloss cari');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-63'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 63 via deploy hook (required)'), 'CI #63 required');
check(! preg_match('/Publish article 63 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #63 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle63'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article63-php.php'), 'audit-article63-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '3/8 Laravel Lanjutan'), 'Progress 3/8');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'Policy') || str_contains($body, 'policy'), 'Soft bridge #64');
check(! str_contains($body, '/artikel/laravel-policy'), 'Tanpa hardlink #64');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article62Seeder.php'), $slug), '#62 hardlink #63');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
