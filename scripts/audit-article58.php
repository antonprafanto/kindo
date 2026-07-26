<?php

/**
 * Audit utama #58 — Routing & Jawaban JSON (Seri 4).
 * Usage: php scripts/audit-article58.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article58Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-routing-json-perpustakaan-api';

echo "=== Audit Artikel #58 — Routing & Jawaban JSON ===\n\n";

$ref = new ReflectionClass(Article58Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php');

check(str_contains($body, '#58 (ini)'), 'Self-ref');
check(str_contains($body, '/api/buku') && str_contains($body, 'Route::get'), 'Route GET /api/buku');
check(str_contains($body, 'response()-&gt;json') || str_contains($body, 'response()->json'), 'response()->json');
check(str_contains($body, 'JSON'), 'JSON');
check(str_contains($body, 'laravel58routeArrow'), 'SVG marker');
check(str_contains($body, 'viewBox="0 0 760 240"'), 'SVG horizontal');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_routing_json_perpustakaan_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-struktur-env-artisan'), 'Link #57');
check(str_contains($body, '/artikel/laravel-instalasi-proyek-pertama'), 'Link #56');
check(! preg_match('/(?<![\w\/"#>])#(?:59|6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #59+');
check(str_contains($body, 'pintu'), 'Analogi pintu');
check(str_contains($body, 'install-dari-nol'), 'Marker install-dari-nol');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-58'), 'Route hook');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 58 via deploy hook (required)'), 'CI #58 step');
check(preg_match('/Publish article 58 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml) !== 1, 'CI #58 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle58'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(str_contains($deploy, 'Article 58 backlink #57') || str_contains($deploy, 'backlink missing on #57'), 'Hardlink #57 diaktifkan');
check(file_exists(__DIR__.'/audit-article58-php.php'), 'audit-article58-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($src, 'published_at <= $prevPublished') || str_contains($src, 'prevPublished'), 'Urutan publish setelah #57');
check(str_contains($body, '3/8'), 'Progress 3/8');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(str_contains($body, 'PHP 8.3+'), 'Syarat PHP 8.3+');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin Laravel 11+ usang');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Hardlink #59');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php'), $slug), '#57 hardlink #58');
check(! str_contains($src, 'laravel-controller-service-eloquent'), 'Tanpa slug lama controller');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
