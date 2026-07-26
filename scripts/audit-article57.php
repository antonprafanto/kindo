<?php

/**
 * Audit utama #57 — Struktur Folder, .env & Artisan (Seri 4).
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

$slug = 'laravel-struktur-env-artisan';

echo "=== Audit Artikel #57 — Struktur Folder, .env & Artisan ===\n\n";

$ref = new ReflectionClass(Article57Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php');

check(str_contains($body, '#57 (ini)'), 'Self-ref');
check(str_contains($body, '.env') && str_contains($body, 'key:generate'), '.env + key:generate');
check(str_contains($body, 'sqlite') && str_contains($body, 'migrate'), 'SQLite + migrate');
check(str_contains($body, 'Artisan') && str_contains($body, 'php artisan list'), 'Artisan list');
check(str_contains($body, 'laravel57structArrow'), 'SVG marker');
check(str_contains($body, 'viewBox="0 0 760 240"') || str_contains($body, "viewBox='0 0 760 240'"), 'SVG alur horizontal 4 langkah');
check(str_contains($body, 'Alur: Folder'), 'SVG judul alur awam');
check(! preg_match('/y="120"[^>]*>[\s\S]{0,80}Artisan/u', $body), 'SVG tanpa Artisan turun menimpa teks');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_struktur_env_artisan_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-instalasi-proyek-pertama'), 'Link #56');
check(! preg_match('/(?<![\w\/"#>])#(?:5[89]|6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #58+');
check(str_contains($body, 'denah'), 'Analogi denah');
check(str_contains($body, 'install-dari-nol'), 'Marker install-dari-nol');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-57'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 57 via deploy hook (required)'), 'CI #57 step');
check(! preg_match('/Publish article 57 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #57 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle57'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(str_contains($deploy, 'Article 57 backlink #56') || str_contains($deploy, 'backlink missing on #56'), 'Hardlink #56 aktif di hook');
check(file_exists(__DIR__.'/audit-article57-php.php'), 'audit-article57-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '2/8'), 'Progress 2/8');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(str_contains($body, 'PHP 8.3+'), 'Syarat PHP 8.3+');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin Laravel 11+ usang');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'routing') && str_contains($body, 'JSON'), 'Soft bridge #58');
check(! str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Tanpa hardlink #58');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php'), $slug), '#56 hardlink #57');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
