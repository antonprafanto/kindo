<?php

/**
 * Audit utama #58 — Controller, Service & Eloquent (Seri 4).
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

$slug = 'laravel-controller-service-eloquent';

echo "=== Audit Artikel #58 — Controller, Service & Eloquent ===\n\n";

$ref = new ReflectionClass(Article58Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php');

check(str_contains($body, '#58 (ini)'), 'Self-ref');
check(str_contains($body, 'BukuService') && str_contains($body, 'BukuController'), 'Service + Controller');
check(str_contains($body, 'Eloquent') && str_contains($body, 'Buku::create'), 'Eloquent create');
check(str_contains($body, 'laravel58ctrlArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_controller_service_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Link #57');
check(! preg_match('/(?<![\w\/"#>])#(?:59|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #59+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#57(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #57');
check(str_contains($body, 'pengatur kode') && str_contains($body, 'layanan'), 'Gloss controller/service');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-58'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 58 via deploy hook (required)'), 'CI #58 required');
check(! preg_match('/Publish article 58 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #58 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle58'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article58-php.php'), 'audit-article58-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '7/8 menuju Capstone Laravel'), 'Progress 7/8');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(str_contains($body, '/artikel/laravel-auth-api-dasar'), 'Hardlink #59');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php'), $slug), '#57 hardlink #58');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'otentikasi'), 'Auth framing');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
