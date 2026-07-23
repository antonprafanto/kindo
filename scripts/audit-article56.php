<?php

/**
 * Audit utama #56 — Laravel routing & JSON (Seri 4).
 * Usage: php scripts/audit-article56.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article56Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-routing-json-perpustakaan-api';

echo "=== Audit Artikel #56 — Laravel Routing & JSON ===\n\n";

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php');

check(str_contains($body, '#56 (ini)'), 'Self-ref');
check(str_contains($body, 'json_encode') && str_contains($body, 'response()-&gt;json'), 'JSON + response json');
check(str_contains($body, 'Route::get'), 'Route::get');
check(str_contains($body, 'laravel56jsonArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_routing_json_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/oop-php-visibility-composition'), 'Link #55');
check(! preg_match('/(?<![\w\/"#>])#(?:5[7-9]|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #57+');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-56'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 56 via deploy hook (required)'), 'CI #56 required');
check(! preg_match('/Publish article 56 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #56 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle56'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article56-php.php'), 'audit-article56-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '6/8 menuju Capstone Laravel'), 'Progress 5/8');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), '#56 hardlink #57');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php'), $slug), '#55 hardlink #56');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
