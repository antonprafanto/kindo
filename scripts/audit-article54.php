<?php

/**
 * Audit utama #54 — property/method/constructor PHP (Seri 4).
 * Usage: php scripts/audit-article54.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article54Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'oop-php-property-method-constructor';

echo "=== Audit Artikel #54 — Property/Method/Constructor PHP ===\n\n";

$ref = new ReflectionClass(Article54Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article54Seeder.php');

check(str_contains($body, '#54 (ini)'), 'Self-ref');
check(str_contains($body, 'class Buku'), 'class Buku');
check(str_contains($body, '__construct'), 'Constructor');
check(str_contains($body, 'oop54phpArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'oop_php_property.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php'), 'Link #53');
check(! preg_match('/(?<![\w\/"#>])#(?:5[5-9]|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #55+');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-54'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 54 via deploy hook (required)'), 'CI #54 required');
check(! preg_match('/Publish article 54 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #54 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle54'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article54-php.php'), 'audit-article54-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '5/8 menuju Capstone Laravel'), 'Progress 5/8');
check(str_contains($body, 'type hint') || str_contains($body, 'Type hint'), 'Type hint awam');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
