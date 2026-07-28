<?php

/**
 * Audit utama #65 — Pagination, Filter & Pencarian (Seri 5).
 * Usage: php scripts/audit-article65.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article65Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-pagination-filter-pencarian';
$prevSlug = 'laravel-eloquent-relasi-peminjaman';

echo "=== Audit Artikel #65 — Pagination, Filter & Pencarian ===\n\n";

$ref = new ReflectionClass(Article65Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article65Seeder.php');

check(str_contains($body, '#65 (ini)'), 'Self-ref');
check(str_contains($body, 'paginate') && str_contains($body, 'array_slice'), 'paginate + array_slice');
check(str_contains($body, '?q=') || str_contains($body, 'q='), 'Pencarian q');
check(str_contains($body, 'status') && str_contains($body, 'aktif'), 'Filter status');
check(str_contains($body, 'laravel65pageArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_pagination_filter_pencarian_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'") && str_contains($src, 'function bodyEn'), 'Seeder field EN + bodyEn()');
check(str_contains($bodyEn, '#65 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN dasar');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Preparation'), 'EN tools-first');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/'.$prevSlug), 'Link #64');
check(! preg_match('/(?<![\w\/"#>])#66(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #66');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#64(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #64');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-65'), 'Route');
check(str_contains($deploy, 'publishArticle65'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article65-php.php'), 'audit-article65-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '2/7'), 'Progress 2/7');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check((str_contains($body, 'Policy') || str_contains($body, 'Authorization')) && ! str_contains($body, '/artikel/laravel-policy-otorisasi-api'), 'Soft bridge #66');
check(! str_contains($body, '/artikel/laravel-api-resource-json'), 'Tanpa hardlink resource');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'Persiapan') && str_contains($body, 'notepad app\Http\Controllers\PeminjamanController.php'), 'Tools-first ID');
check(str_contains($body, 'saring') && str_contains($body, 'potong'), 'Urutan saring-cari-potong');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
