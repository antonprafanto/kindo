<?php

/**
 * Audit utama #60 — Controller, Service & Eloquent (Seri 4).
 * Usage: php scripts/audit-article60.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article60Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-controller-service-eloquent';

echo "=== Audit Artikel #60 — Controller, Service & Eloquent ===\n\n";

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article60Seeder.php');

check(str_contains($body, '#60 (ini)'), 'Self-ref');
check(str_contains($body, 'BukuController') && str_contains($body, 'BukuService'), 'Controller + Service');
check(str_contains($body, 'Eloquent') && str_contains($body, 'make:model'), 'Eloquent + make:model');
check(str_contains($body, 'laravel60cseArrow'), 'SVG marker');
check(str_contains($body, 'viewBox="0 0 760 240"'), 'SVG horizontal');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_controller_service_eloquent_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Link #59');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Link #58');
check(str_contains($body, '/artikel/laravel-struktur-env-artisan'), 'Link #57');
check(str_contains($body, '/artikel/laravel-instalasi-proyek-pertama'), 'Link #56');
check(! preg_match('/(?<![\w\/"#>])#(?:6[1-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #61+');
check(str_contains($body, 'loket') || str_contains($body, 'dapur'), 'Analogi loket/dapur');
check(str_contains($body, 'install-dari-nol'), 'Marker install-dari-nol');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'terminal kedua'), 'Petunjuk tools awam');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-60'), 'Route hook');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 60 via deploy hook (required)'), 'CI #60 step');
check(preg_match('/Publish article 60 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml) !== 1, 'CI #60 required (tanpa continue-on-error)');
check(str_contains($deploy, 'publishArticle60'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(str_contains($deploy, 'Article 60 backlink #59') || str_contains($deploy, 'backlink missing on #59'), 'Hardlink #59→#60 aktif');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php'), $slug), '#59 hardlink #60');
check(file_exists(__DIR__.'/audit-article60-php.php'), 'audit-article60-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($src, 'prevPublished'), 'Urutan publish setelah #59');
check(str_contains($body, '5/8'), 'Progress 5/8');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(str_contains($body, 'PHP 8.3+'), 'Syarat PHP 8.3+');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin Laravel 11+ usang');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, 'Auth API') || str_contains($body, 'kartu anggota') || str_contains($body, '/artikel/laravel-auth-api-dasar'), 'Soft/hard bridge #61');
check(str_contains($body, '/artikel/laravel-auth-api-dasar'), 'Hardlink #61');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(! str_contains($src, 'capstone-api-perpustakaan-laravel'), 'Tanpa slug Capstone usang');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
