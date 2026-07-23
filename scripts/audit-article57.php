<?php

/**
 * Audit utama #57 — Request & Form Request (Seri 4).
 * Usage: php scripts/audit-article57.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article57Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-request-validasi-api';

echo "=== Audit Artikel #57 — Request & Form Request ===\n\n";

$ref = new ReflectionClass(Article57Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php');

check(str_contains($body, '#57 (ini)'), 'Self-ref');
check(str_contains($body, 'validasiBuku') && str_contains($body, 'FormRequest'), 'Validasi + FormRequest');
check(str_contains($body, '422'), 'Status 422');
check(str_contains($body, 'laravel57reqArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_request_validasi_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Link #56');
check(! preg_match('/(?<![\w\/"#>])#(?:5[89]|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #58+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#56(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #56');
check(! str_contains($body, 'Unprocessable Entity'), 'Tanpa Unprocessable');
check(! str_contains($body, 'Client /'), 'SVG Browser bukan Client');
check(str_contains($body, 'required') && str_contains($body, 'wajib'), 'Gloss required');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-57'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 57 via deploy hook (required)'), 'CI #57 required');
check(! preg_match('/Publish article 57 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #57 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle57'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article57-php.php'), 'audit-article57-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '5/8 menuju Capstone Laravel'), 'Progress 5/8');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '/artikel/laravel-controller'), 'Belum hardlink #58');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php'), $slug), '#56 hardlink #57');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
