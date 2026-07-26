<?php

/**
 * Content / checklist audit #58.
 * Usage: php scripts/audit-article58-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article58Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #58 ===\n\n";

$ref = new ReflectionClass(Article58Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php');
$slug = 'laravel-routing-json-perpustakaan-api';

check(str_contains($body, '#58 (ini)'), 'Self-ref #58 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:59|6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #59+');
check(str_contains($body, '/artikel/laravel-struktur-env-artisan'), 'Link #57');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_routing_json_perpustakaan_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel58routeArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 3, '≥3 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-58'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '3/8'), 'Progress 3/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'STOP AUDIT') && ! str_contains($body, 'oke deploy'), 'Tanpa suara editor');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article58.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article58-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article58-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article58-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'pintu'), 'Narasi pintu');
check(str_contains($body, '/api/buku'), 'Path /api/buku');
check(str_contains($body, 'routes/web.php'), 'File web.php');
check(! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'PHP 8.3+'), 'Versi PHP awam');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Hardlink #59');
check(! str_contains($body, 'Eloquent') && ! str_contains($body, 'scaffolding'), 'Tanpa Eloquent/scaffolding dingin');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'install-dari-nol'), 'Aturan install-dari-nol');
check(str_contains($body, 'demo()'), 'Demo fungsi');
$thin = preg_match_all('/(?<![\w\/"#>(ini)\s])#5[3-8](?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '');
check($thin === 0, 'Thin/bare #53-58 = 0');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
