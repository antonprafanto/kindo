<?php

/**
 * Audit utama #56 — Instal PHP, Composer & Proyek Laravel (Seri 4).
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

$slug = 'laravel-instalasi-proyek-pertama';

echo "=== Audit Artikel #56 — Instal PHP, Composer & Proyek Laravel ===\n\n";

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php');

check(str_contains($body, '#56 (ini)'), 'Self-ref');
check(str_contains($body, 'create-project') && str_contains($body, 'composer create-project'), 'create-project');
check(str_contains($body, 'artisan serve') && str_contains($body, 'php artisan --version'), 'artisan serve + version');
check(str_contains($body, 'Composer') && str_contains($body, 'Laragon'), 'Composer + Laragon');
check(str_contains($body, 'laravel56installArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_instalasi_proyek_pertama_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/oop-php-visibility-composition'), 'Link #55');
check(str_contains($body, '/artikel/oop-php-property-method-constructor'), 'Link #54');
check(! preg_match('/(?<![\w\/"#>])#(?:5[7-9]|6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #57+');
check(str_contains($body, 'mendirikan toko'), 'Analogi toko');
check(str_contains($body, 'install-dari-nol'), 'Marker install-dari-nol');

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
check(str_contains($body, '1/8'), 'Progress 1/8');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(str_contains($body, 'PHP 8.3+'), 'Syarat PHP 8.3+');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin Laravel 11+ usang');
check(! str_contains($body, 'PHP 8.2+'), 'Tanpa syarat PHP 8.2+ usang');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'struktur folder') && str_contains($body, '.env') && str_contains($body, 'Artisan'), 'Soft bridge #57');
check(! str_contains($body, '/artikel/laravel-struktur-env-artisan'), 'Tanpa hardlink #57');
check(! str_contains($body, '/artikel/laravel-request-validasi-api'), 'Tanpa hardlink slug lama #57');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php'), $slug), '#55 hardlink #56');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
