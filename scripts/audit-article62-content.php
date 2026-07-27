<?php

/**
 * Content / checklist audit #62 — Capstone API Perpustakaan.
 * Usage: php scripts/audit-article62-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article62Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'capstone-api-perpustakaan-laravel';

echo "=== Content / checklist audit #62 ===\n\n";

$ref = new ReflectionClass(Article62Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article62Seeder.php');

check(str_contains($body, '#62 (ini)'), 'Self-ref #62 (ini)');
check(! preg_match('/(?<![\w\/"#>])#63(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #63');
check(str_contains($body, '/artikel/laravel-auth-api-dasar'), 'Link #61');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_capstone_api_perpustakaan_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel62capstoneArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 3, '≥3 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-62'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '7/8'), 'Progress 7/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor');
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>(ini)\s])#5[6-9](?!\s*\(ini\))/', $plain), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article62.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article62-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article62-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article62-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'Capstone') && str_contains($body, 'login'), 'Narasi Capstone/login');
check(str_contains($body, '/api/login') && str_contains($body, 'POST /api/buku'), 'Path login + POST buku');
check(str_contains($body, 'BukuController'), 'BukuController');
check(! str_contains($body, 'Pin ') && ! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'PHP 8.3+'), 'Versi PHP awam');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'install-dari-nol'), 'Aturan install-dari-nol');
check(str_contains($body, 'demo()'), 'Demo fungsi');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'Browser'), 'Daftar alat awam');
check(str_contains($body, 'Explorer'), 'Explorer di daftar alat');
check(str_contains($body, 'cd C:\\laragon\\www\\perpustakaan-api') || str_contains($body, 'perpustakaan-api'), 'cd folder proyek');
check(str_contains($body, 'terminal kedua'), 'Tip terminal kedua');
check(str_contains($body, 'Terminal mana') || str_contains($body, 'Shell XAMPP'), 'FAQ terminal');
check(str_contains($body, 'Start Menu') || str_contains($body, 'CMD/PowerShell'), 'Peringatan terminal salah');
check(str_contains($body, 'curl.exe'), 'curl.exe');
check(str_contains($body, 'Opsi C') || str_contains($body, 'Postman'), 'Opsi alat berjendela');
check(str_contains($body, 'salin token') || str_contains($body, 'Salin token') || str_contains($body, '"token"'), 'Tip salin token');
check(! str_contains($body, '%%{http_code}'), 'Tanpa %%{http_code} membingungkan');
check(str_contains($body, 'Accept: application/json'), 'Header Accept');
$withoutLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '';
$bare61 = preg_match_all('/#61(?!\s*\(ini\))/', $withoutLinks);
check($bare61 === 0, 'Tanpa bare #61 ('.$bare61.')');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
