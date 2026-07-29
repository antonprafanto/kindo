<?php

/**
 * Content / checklist audit #70 Capstone Pinjam & Kembalikan.
 * Usage: php scripts/audit-article70-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article70Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #70 ===\n\n";

$ref = new ReflectionClass(Article70Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article70Seeder.php');
$slug = 'capstone-pinjam-kembali-laravel';
$prevSlug = 'laravel-rate-limiting-api';

check(str_contains($body, '#70 (ini)'), 'Self-ref #70 (ini)');
check(! preg_match('/(?<![\w\/"#>])#71(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #71');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#69(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #69 di prosa');
check(! preg_match('/(?<![\w\/"#>])#68(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #68 di prosa');
check(str_contains($body, '/artikel/'.$prevSlug), 'Link #69');
check(str_contains($body, '/artikel/laravel-eloquent-relasi-peminjaman'), 'Link #64');
check(str_contains($body, '/artikel/laravel-pagination-filter-pencarian'), 'Link #65');
check(str_contains($body, '/artikel/laravel-policy-otorisasi-api'), 'Link #66');
check(str_contains($body, '/artikel/laravel-api-resource-json'), 'Link #67');
check(str_contains($body, '/artikel/laravel-feature-test-api'), 'Link #68');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'capstone_pinjam_kembali_laravel_demo.php'), 'File contoh demo');
check(str_contains($body, 'alur-cek.php'), 'Mid file alur-cek');
check(str_contains($body, 'curl.exe'), 'curl.exe Windows awam');
check(str_contains($body, '404') && str_contains($body, 'rute mungkin belum'), 'Gloss curl 404 rute belum');
check(str_contains($body, 'cara menguji bagian ini') && str_contains($body, 'capstone_pinjam_kembali_laravel_demo.php'), 'Cara uji demo lengkap');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel70capArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-70'), 'Route hook');
check(str_contains($body, '7/7'), 'Progress 7/7 tamat');
check(str_contains($body, 'tamat'), 'Framing tamat');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'Persiapan') && str_contains($body, 'tests\Feature'), 'Tools-first ID');
check(str_contains($body, 'routes\api.php') || str_contains($body, 'routes\\api.php'), 'Path routes/api');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'Belum perlu hardlink') && ! str_contains($body, 'soft, belum hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article70.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article70-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article70-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article70-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'PHP biasa') || str_contains($body, 'Kenapa PHP'), 'Narasi PHP dulu');
check(str_contains($body, 'petugas') || str_contains($body, 'perpustakaan'), 'Analogi petugas/perpustakaan');
check(str_contains($body, 'authorize') && str_contains($body, 'assertJsonStructure'), 'Gloss Policy/Test');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'Piranti Bergerak') && ! preg_match('/\/artikel\/[^"]*piranti/i', $body), 'Soft bridge Piranti Bergerak');
check(str_contains($bodyEn, 'Mobile Devices') && ! preg_match('/\/artikel\/[^"]*mobile/i', $bodyEn), 'EN soft Mobile Devices');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek Laravel');
check(! str_contains($body, 'endpoint'), 'Tanpa endpoint');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'pinjam') || str_contains($body, 'peminjaman'), 'Domain pinjam');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'"), 'Field EN ada');
check(str_contains($bodyEn, '#70 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN ada');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Install-from-scratch'), 'Tools-first EN');
check(! str_contains($body, 'supaya UI '), 'Tanpa jargon UI');
check(substr_count($body, '<a ') - substr_count($body, '</a>') === 0, 'Thin anchor balance');
$noSvg = preg_replace('/<svg\b.*?<\/svg>/is', '', $body) ?? $body;
$noA = preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $noSvg) ?? $noSvg;
check(! preg_match('/(?<![\w\/"#>])#6[0-9](?!\s*\(ini\))/', strip_tags($noA)), 'Thin/bare numbered = 0');
check(str_contains($body, 'satu terminal') || str_contains($body, 'satu terminal sebenarnya cukup'), 'Satu terminal cukup');
check(str_contains($body, 'terminal kedua'), 'Terminal kedua dijelaskan');
check(str_contains($bodyEn, 'curl.exe'), 'EN curl.exe');
check(str_contains($bodyEn, '404') && str_contains($bodyEn, 'route may not be installed'), 'EN gloss curl 404');
check(str_contains($bodyEn, 'how to test this part') && str_contains($bodyEn, 'capstone_pinjam_kembali_laravel_demo.php'), 'EN cara uji demo');
check(str_contains($body, 'php alur-cek.php') && str_contains($body, 'demo(') && str_contains($body, 'curl.exe'), '3-tier alur-cek → demo → curl');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
