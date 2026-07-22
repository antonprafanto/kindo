<?php

/**
 * Audit utama #53 — OOP PHP (Seri 4).
 * Usage: php scripts/audit-article53.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article53Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'mengenal-oop-cara-berpikir-dengan-objek-php';
$oldSlug = 'http-rest-kontrak-stub-flask-oop';

echo "=== Audit Artikel #53 — OOP PHP ===\n\n";

$ref = new ReflectionClass(Article53Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article53Seeder.php');

check(str_contains($body, '#53 (ini)'), 'Self-ref');
check(str_contains($body, 'class Buku'), 'class Buku');
check(str_contains($body, 'oop53phpArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'oop_php_dasar.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug baru di seeder');
check(str_contains($src, $oldSlug), 'Tombstone slug lama');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-53'), 'Route');
check(str_contains($yml, $slug), 'CI slug baru');
check(str_contains($yml, 'Publish article 53 via deploy hook (required)'), 'CI #53 required');
check(! preg_match('/Publish article 53 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #53 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle53'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug baru');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php'), $slug), 'Backlink #52');
check(file_exists(__DIR__.'/audit-article53-php.php'), 'audit-article53-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
