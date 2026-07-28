<?php

/**
 * Audit utama #64 — Relasi Eloquent (Seri 5).
 * Usage: php scripts/audit-article64.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article64Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-eloquent-relasi-peminjaman';

echo "=== Audit Artikel #64 — Relasi Eloquent ===\n\n";

$ref = new ReflectionClass(Article64Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article64Seeder.php');

check(str_contains($body, '#64 (ini)'), 'Self-ref');
check(str_contains($body, 'belongsTo') && str_contains($body, 'hasMany'), 'belongsTo + hasMany');
check(str_contains($body, 'relasi') && str_contains($body, 'peminjaman'), 'Relasi awam');
check(str_contains($body, 'buku_id') && str_contains($body, 'anggota_id'), 'Gloss kunci penghubung');
check(str_contains($body, 'laravel64relasiArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_eloquent_relasi_peminjaman_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'") && str_contains($src, 'function bodyEn'), 'Seeder field EN + bodyEn()');
check(str_contains($bodyEn, '#64 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN dasar');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Preparation'), 'EN tools-first');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-crud-api-buku-ubah-hapus'), 'Link #63');
check(! preg_match('/(?<![\w\/"#>])#65(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #65');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#63(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #63');
check(str_contains($body, 'anggota_id'), 'Gloss anggota_id');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-64'), 'Route');
check(str_contains($deploy, 'publishArticle64'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article64-php.php'), 'audit-article64-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '1/7'), 'Progress 1/7');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(substr_count($body, '/artikel/laravel-pagination-filter-pencarian') >= 3, 'Hardlink #65 3×');
check(substr_count($bodyEn, '/artikel/laravel-pagination-filter-pencarian') >= 3, 'EN hardlink #65 3×');
check(! str_contains($body, '/artikel/laravel-api-resource-json'), 'Tanpa slug resource usang');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article63Seeder.php'), $slug), '#63 hardlink #64');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
