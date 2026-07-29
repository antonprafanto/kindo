<?php

/**
 * Audit utama #69 — Rate Limiting API (Seri 5).
 * Usage: php scripts/audit-article69.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article69Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-rate-limiting-api';
$prevSlug = 'laravel-feature-test-api';

echo "=== Audit Artikel #69 — Rate Limiting API ===\n\n";

$ref = new ReflectionClass(Article69Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article69Seeder.php');

check(str_contains($body, '#69 (ini)'), 'Self-ref');
check(str_contains($body, 'RateLimiter') && str_contains($body, 'throttle'), 'RateLimiter + throttle');
check(str_contains($body, '429'), 'HTTP 429');
check(str_contains($body, 'laravel69rateArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_rate_limiting_api_demo.php'), 'File contoh');
check(str_contains($body, 'batas-cek.php'), 'Mid file batas-cek');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'") && str_contains($src, 'function bodyEn'), 'Seeder field EN + bodyEn()');
check(str_contains($bodyEn, '#69 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN dasar');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Preparation'), 'EN tools-first');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/'.$prevSlug), 'Link #68');
check(! preg_match('/(?<![\w\/"#>])#70(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #70');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#68(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #68');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
check(str_contains($routes, 'publish-article-69'), 'Route');
check(str_contains($deploy, 'publishArticle69'), 'DeployController');
check(str_contains($deploy, 'laravel-rate-limiting-api') && str_contains($deploy, 'Article69Seeder'), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article69-php.php'), 'audit-article69-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '6/7'), 'Progress 6/7');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'Capstone') && ! str_contains($body, '/artikel/capstone-pinjam-kembali-laravel'), 'Soft bridge Capstone');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'Persiapan') && str_contains($body, 'notepad app\Providers\AppServiceProvider.php'), 'Tools-first ID');
check(str_contains($body, 'batas-cek.php') && str_contains($body, 'demo('), '3-tier batas-cek + demo');
check(str_contains($body, 'curl.exe'), 'curl.exe spam test');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
