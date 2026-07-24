<?php

/**
 * Audit utama #55 — visibility & composition PHP (Seri 4).
 * Usage: php scripts/audit-article55.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article55Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'oop-php-visibility-composition';

echo "=== Audit Artikel #55 — Visibility & Composition PHP ===\n\n";

$ref = new ReflectionClass(Article55Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php');

check(str_contains($body, '#55 (ini)'), 'Self-ref');
check(str_contains($body, 'class Buku') && str_contains($body, 'class Katalog'), 'Buku + Katalog');
check(str_contains($body, 'private'), 'private');
check(str_contains($body, 'oop55phpArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'oop_php_visibility.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/oop-php-property-method-constructor'), 'Link #54');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php'), 'Link #53');
check(! preg_match('/(?<![\w\/"#>])#(?:5[6-9]|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #56+');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-55'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 55 via deploy hook (required)'), 'CI #55 required');
check(! preg_match('/Publish article 55 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #55 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle55'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article55-php.php'), 'audit-article55-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '8/8 Capstone Laravel selesai'), 'Progress 5/8');
check(str_contains($body, 'Composition') || str_contains($body, 'composition'), 'Composition');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Hardlink Laravel #56');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
