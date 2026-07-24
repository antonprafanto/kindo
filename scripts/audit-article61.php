<?php

/**
 * Audit utama #61 — CRUD API Buku: Ubah & Hapus (Seri 5).
 * Usage: php scripts/audit-article61.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article61Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-crud-api-buku-ubah-hapus';

echo "=== Audit Artikel #61 — CRUD API Buku Ubah & Hapus ===\n\n";

$ref = new ReflectionClass(Article61Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article61Seeder.php');

check(str_contains($body, '#61 (ini)'), 'Self-ref');
check(str_contains($body, '404') && str_contains($body, '204') && str_contains($body, '422'), '404 + 204 + 422');
check(str_contains($body, 'CRUD') || str_contains($body, 'ubah'), 'CRUD framing');
check(str_contains($body, 'laravel61crudArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_crud_buku_ubah_hapus_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/capstone-api-perpustakaan-laravel'), 'Link #60');
check(! preg_match('/(?<![\w\/"#>])#62(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #62+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#60(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #60');
check(str_contains($body, 'Belum diizinkan'), 'Gloss 401 awam');
check(str_contains($body, 'Buku tidak ketemu'), 'Gloss 404 awam');
check(str_contains($body, 'Isian belum rapi'), 'Gloss 422 awam');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-61'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 61 via deploy hook (required)'), 'CI #61 required');
check(! preg_match('/Publish article 61 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #61 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle61'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article61-php.php'), 'audit-article61-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '1/8 Laravel Lanjutan'), 'Progress 1/8');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'Relasi Eloquent'), 'Soft bridge #62');
check(str_contains($body, 'bukti masuk'), 'Gloss bukti masuk');
check(str_contains($body, 'destroy'), 'destroy framing');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article60Seeder.php'), $slug), '#60 hardlink #61');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
