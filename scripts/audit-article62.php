<?php

/**
 * Audit utama #62 — Relasi Eloquent: Anggota & Peminjaman (Seri 5).
 * Usage: php scripts/audit-article62.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article62Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-eloquent-relasi-peminjaman';

echo "=== Audit Artikel #62 — Relasi Eloquent Anggota & Peminjaman ===\n\n";

$ref = new ReflectionClass(Article62Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article62Seeder.php');

check(str_contains($body, '#62 (ini)'), 'Self-ref');
check(str_contains($body, 'hasMany') && str_contains($body, 'belongsTo'), 'hasMany + belongsTo');
check(str_contains($body, 'anggota_id') && str_contains($body, 'buku_id'), 'Kunci asing');
check(str_contains($body, 'laravel62relasiArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_eloquent_relasi_peminjaman_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-crud-api-buku-ubah-hapus'), 'Link #61');
check(str_contains($body, '/artikel/capstone-api-perpustakaan-laravel'), 'Link #60');
check(! preg_match('/(?<![\w\/"#>])#63(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #63+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#61(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #61');
check(! preg_match('/(?<![\w\/"#>])#60(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #60');
check(str_contains($body, 'Anggota tidak ketemu'), 'Gloss 404 anggota');
check(str_contains($body, 'Kunci asing') || str_contains($body, 'kunci asing'), 'Gloss kunci asing');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-62'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 62 via deploy hook (required)'), 'CI #62 required');
check(! preg_match('/Publish article 62 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #62 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle62'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article62-php.php'), 'audit-article62-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '2/8 Laravel Lanjutan'), 'Progress 2/8');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'Pagination') || str_contains($body, 'pagination'), 'Soft bridge #63');
check(! str_contains($body, '/artikel/laravel-pagination'), 'Tanpa hardlink #63');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article61Seeder.php'), $slug), '#61 hardlink #62');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
