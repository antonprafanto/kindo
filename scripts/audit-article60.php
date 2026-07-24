<?php

/**
 * Audit utama #60 — Capstone API Perpustakaan Laravel (Seri 4).
 * Usage: php scripts/audit-article60.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article60Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'capstone-api-perpustakaan-laravel';

echo "=== Audit Artikel #60 — Capstone API Perpustakaan ===\n\n";

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article60Seeder.php');

check(str_contains($body, '#60 (ini)'), 'Self-ref');
check(str_contains($body, 'bukti_masuk') && str_contains($body, '401') && str_contains($body, '422'), 'Bukti + 401 + 422');
check(str_contains($body, 'Capstone'), 'Capstone framing');
check(str_contains($body, 'laravel60capArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_capstone_perpustakaan_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/laravel-auth-api-dasar'), 'Link #59');
check(! preg_match('/(?<![\w\/"#>])#61(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #61+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#59(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #59');
check(str_contains($body, 'Belum diizinkan'), 'Gloss 401 awam');
check(str_contains($body, 'Isian belum rapi'), 'Gloss 422 awam');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-60'), 'Route');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 60 via deploy hook (required)'), 'CI #60 required');
check(! preg_match('/Publish article 60 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #60 tidak continue-on-error');
check(str_contains($deploy, 'publishArticle60'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article60-php.php'), 'audit-article60-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*true/", $src) === 1, 'is_featured true (Capstone)');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '8/8 Capstone Laravel'), 'Progress 8/8');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php'), $slug), '#59 hardlink #60');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'Laravel lanjutan') || str_contains($body, 'laravel-crud-api-buku-ubah-hapus'), 'Bridge Laravel lanjutan / #61');
check(str_contains($body, 'laravel-crud-api-buku-ubah-hapus'), 'Hardlink #61');
check(str_contains($body, 'Indeks Seri 4'), 'Indeks Seri 4');
check(str_contains($body, 'bukti masuk'), 'Gloss bukti masuk');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
