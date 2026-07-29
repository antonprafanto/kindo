<?php

/**
 * Audit utama #68 — Feature Test API (Seri 5).
 * Usage: php scripts/audit-article68.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article68Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-feature-test-api';
$prevSlug = 'laravel-api-resource-json';

echo "=== Audit Artikel #68 — Feature Test API ===\n\n";

$ref = new ReflectionClass(Article68Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article68Seeder.php');

check(str_contains($body, '#68 (ini)'), 'Self-ref');
check(str_contains($body, 'assertJson') && str_contains($body, 'assertStatus'), 'assertJson + assertStatus');
check(str_contains($body, 'Feature Test') && str_contains($body, 'assertJsonPath'), 'Feature Test + assertJsonPath');
check(str_contains($body, 'laravel68testArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_feature_test_api_demo.php'), 'File contoh');
check(str_contains($body, 'uji-cek.php'), 'Mid file uji-cek');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'") && str_contains($src, 'function bodyEn'), 'Seeder field EN + bodyEn()');
check(str_contains($bodyEn, '#68 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN dasar');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Preparation'), 'EN tools-first');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/'.$prevSlug), 'Link #67');
check(! preg_match('/(?<![\w\/"#>])#69(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #69');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#67(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #67');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-68'), 'Route');
check(str_contains($deploy, 'publishArticle68'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article68-php.php'), 'audit-article68-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '5/7'), 'Progress 5/7');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'Rate Limiting') && ! str_contains($body, '/artikel/laravel-rate-limiting-api'), 'Soft bridge Rate Limiting');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'Persiapan') && str_contains($body, 'notepad tests\Feature\PeminjamanResourceTest.php'), 'Tools-first ID');
check(str_contains($body, 'uji-cek.php') && str_contains($body, 'demo('), '3-tier uji-cek + demo');
check(str_contains($body, 'php artisan test') || str_contains($body, 'vendor/bin/phpunit'), 'php artisan test');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
