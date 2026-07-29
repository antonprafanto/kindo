<?php

/**
 * Audit utama #67 — API Resource JSON (Seri 5).
 * Usage: php scripts/audit-article67.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article67Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-api-resource-json';
$prevSlug = 'laravel-policy-otorisasi-api';

echo "=== Audit Artikel #67 — API Resource JSON ===\n\n";

$ref = new ReflectionClass(Article67Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article67Seeder.php');

check(str_contains($body, '#67 (ini)'), 'Self-ref');
check(str_contains($body, 'JsonResource') && str_contains($body, 'PeminjamanResource'), 'JsonResource + PeminjamanResource');
check(str_contains($body, 'toArray') && str_contains($body, 'status_label'), 'toArray + status_label');
check(str_contains($body, 'laravel67resourceArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_api_resource_json_demo.php'), 'File contoh');
check(str_contains($body, 'rapikan-cek.php'), 'Mid file rapikan-cek');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'") && str_contains($src, 'function bodyEn'), 'Seeder field EN + bodyEn()');
check(str_contains($bodyEn, '#67 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN dasar');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Preparation'), 'EN tools-first');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/'.$prevSlug), 'Link #66');
check(! preg_match('/(?<![\w\/"#>])#68(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #68');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#66(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #66');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-67'), 'Route');
check(str_contains($deploy, 'publishArticle67'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article67-php.php'), 'audit-article67-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '4/7'), 'Progress 4/7');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'Feature Test') && ! str_contains($body, '/artikel/laravel-feature-test-api'), 'Soft bridge Feature Test');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'Persiapan') && str_contains($body, 'notepad app\Http\Resources\PeminjamanResource.php'), 'Tools-first ID');
check(str_contains($body, 'rapikan-cek.php') && str_contains($body, 'demo('), '3-tier rapikan-cek + demo');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
