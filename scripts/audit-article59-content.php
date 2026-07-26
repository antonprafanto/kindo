<?php

/**
 * Content / checklist audit #59.
 * Usage: php scripts/audit-article59-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article59Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #59 ===\n\n";

$ref = new ReflectionClass(Article59Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php');
$slug = 'laravel-request-validasi-api';

check(str_contains($body, '#59 (ini)'), 'Self-ref #59 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #60+');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Link #58');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_request_validasi_api_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel59reqArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 3, '≥3 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-59'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '4/8'), 'Progress 4/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'STOP AUDIT') && ! str_contains($body, 'oke deploy'), 'Tanpa suara editor');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article59.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article59-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article59-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article59-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'satpam') || str_contains($body, 'slip'), 'Narasi satpam');
check(str_contains($body, 'POST /api/buku') || str_contains($body, 'POST'), 'Path POST');
check(str_contains($body, 'StoreBukuRequest'), 'StoreBukuRequest');
check(! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'PHP 8.3+'), 'Versi PHP awam');
check((str_contains($body, 'pengatur kode') || str_contains($body, 'tabel') || str_contains($body, 'file pengatur')) && ! str_contains($body, '/artikel/laravel-controller-service-eloquent'), 'Soft bridge #60 tanpa hardlink');
check(! str_contains($body, 'Eloquent') && ! str_contains($body, 'Sanctum') && ! str_contains($body, 'scaffolding'), 'Tanpa Eloquent/Sanctum/scaffolding dingin');
check(! str_contains($body, 'GUI') && ! str_contains($body, 'namespace'), 'Tanpa GUI/namespace dingin');
check(str_contains($body, 'max:120') && str_contains($body, 'maksimal'), 'Gloss max:120');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'install-dari-nol'), 'Aturan install-dari-nol');
check(str_contains($body, 'demo()'), 'Demo fungsi');
check(str_contains($body, 'terminal kedua') || str_contains($body, 'jendela terminal kedua'), 'Petunjuk terminal kedua');
check(str_contains($body, 'curl.exe') && str_contains($body, 'Invoke-RestMethod'), 'Opsi uji Windows curl/PowerShell');
check(str_contains($body, 'editor teks') || str_contains($body, 'Buka editor'), 'Petunjuk editor teks');
check(str_contains($body, 'Alat yang dipakai') || str_contains($body, 'PowerShell'), 'Daftar alat awam');
$thin = preg_match_all('/(?<![\w\/"#>(ini)\s])#5[3-9](?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '');
check($thin === 0, 'Thin/bare #53-59 = 0');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
