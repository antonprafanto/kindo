<?php

/**
 * Audit utama #61 — Auth API Dasar (Seri 4).
 * Usage: php scripts/audit-article61.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article61Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-auth-api-dasar';

echo "=== Audit Artikel #61 — Auth API Dasar ===\n\n";

$ref = new ReflectionClass(Article61Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article61Seeder.php');

check(str_contains($body, '#61 (ini)'), 'Self-ref');
check(str_contains($body, 'AuthController') && str_contains($body, 'Sanctum'), 'AuthController + Sanctum');
check(str_contains($body, 'createToken') || str_contains($body, 'Bearer'), 'Token / Bearer');
check(str_contains($body, 'laravel61authArrow'), 'SVG marker');
check(str_contains($body, 'viewBox="0 0 760 240"'), 'SVG horizontal');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_auth_api_dasar_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-controller-service-eloquent'), 'Link #60');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Link #59');
check(str_contains($body, '/artikel/laravel-struktur-env-artisan'), 'Link #57');
check(str_contains($body, '/artikel/laravel-instalasi-proyek-pertama'), 'Link #56');
check(! preg_match('/(?<![\w\/"#>])#(?:6[2-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #62+');
check(str_contains($body, 'kartu anggota') || str_contains($body, 'Bearer'), 'Analogi kartu');
check(str_contains($body, 'install-dari-nol'), 'Marker install-dari-nol');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'terminal kedua'), 'Petunjuk tools awam');
check(str_contains($body, 'curl.exe'), 'curl.exe uji');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-61'), 'Route hook');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 61 via deploy hook (required)'), 'CI #61 step');
check(preg_match('/Publish article 61 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml) !== 1, 'CI #61 required (tanpa continue-on-error)');
check(str_contains($deploy, 'publishArticle61'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(str_contains($deploy, 'Article 61 backlink #60') || str_contains($deploy, 'backlink missing on #60'), 'Hardlink #60→#61 aktif');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article60Seeder.php'), $slug), '#60 hardlink #61');
check(file_exists(__DIR__.'/audit-article61-php.php'), 'audit-article61-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($src, 'prevPublished'), 'Urutan publish setelah #60');
check(str_contains($body, '6/8'), 'Progress 6/8');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(str_contains($body, 'PHP 8.3+'), 'Syarat PHP 8.3+');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin Laravel 11+ usang');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, 'Capstone') || str_contains($body, 'tambah buku'), 'Soft bridge #62');
check(str_contains($body, '/artikel/capstone-api-perpustakaan-laravel'), 'Hardlink #62 aktif');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(! str_contains($src, 'laravel-crud-api-buku-ubah-hapus'), 'Tanpa slug CRUD Seri 5 usang');
check(! str_contains($body, 'Seri 5'), 'Tanpa framing Seri 5 usang');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
