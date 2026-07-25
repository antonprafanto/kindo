<?php

/**
 * Audit utama #64 — Authorization Policy (Seri 5).
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

$slug = 'laravel-policy-otorisasi-api';

echo "=== Audit Artikel #64 — Authorization Policy ===\n\n";

$ref = new ReflectionClass(Article64Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article64Seeder.php');

check(str_contains($body, '#64 (ini)'), 'Self-ref');
check(str_contains($body, 'authorize') && str_contains($body, 'Policy'), 'authorize + Policy');
check(str_contains($body, 'aturan izin'), 'Aturan izin awam');
check(str_contains($body, 'Tidak punya izin'), 'Gloss 403');
check(str_contains($body, 'laravel64policyArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_policy_otorisasi_api_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-pagination-filter-pencarian'), 'Link #63');
check(str_contains($body, '/artikel/laravel-eloquent-relasi-peminjaman'), 'Link #62');
check(! preg_match('/(?<![\w\/"#>])#65(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #65');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#63(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #63');
check(! preg_match('/(?<![\w\/"#>])#62(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #62');
check(str_contains($body, 'anggota_id'), 'Gloss anggota_id');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-64'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 64 via deploy hook (required)'), 'CI #64 required');
check(! preg_match('/Publish article 64 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #64 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle64'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article64-php.php'), 'audit-article64-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '4/8 Laravel Lanjutan'), 'Progress 4/8');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check((str_contains($body, 'API Resource') || str_contains($body, 'resource JSON')) && ! str_contains($body, '/artikel/laravel-api-resource'), 'Soft bridge #65');
check(! str_contains($body, '/artikel/laravel-api-resource-json'), 'Tanpa hardlink #65');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article63Seeder.php'), $slug), '#63 hardlink #64');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
