<?php

/**
 * Content / checklist audit #66 authorization policy.
 * Usage: php scripts/audit-article66-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article66Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #66 ===\n\n";

$ref = new ReflectionClass(Article66Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article66Seeder.php');
$slug = 'laravel-policy-otorisasi-api';
$prevSlug = 'laravel-pagination-filter-pencarian';

check(str_contains($body, '#66 (ini)'), 'Self-ref #66 (ini)');
check(! preg_match('/(?<![\w\/"#>])#67(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #67');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#65(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #65 di prosa');
check(! preg_match('/(?<![\w\/"#>])#64(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #64 di prosa');
check(str_contains($body, '/artikel/'.$prevSlug), 'Link #65');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_policy_otorisasi_api_demo.php'), 'File contoh');
check(str_contains($body, 'izin-cek.php'), 'Mid file izin-cek');
check(str_contains($body, 'curl.exe'), 'curl.exe Windows awam');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel66policyArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-66'), 'Route hook');
check(str_contains($body, '3/7'), 'Progress 3/7');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'Persiapan') && str_contains($body, 'notepad app\Http\Controllers\PeminjamanController.php'), 'Tools-first ID');
check(str_contains($body, 'PeminjamanPolicy.php') || str_contains($body, 'app\\Policies\\PeminjamanPolicy'), 'Path Policy');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'Belum perlu hardlink') && ! str_contains($body, 'soft, belum hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article66.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article66-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article66-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article66-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'PHP biasa') || str_contains($body, 'Kenapa PHP'), 'Narasi PHP dulu');
check(str_contains($body, 'slip pinjam') || str_contains($body, 'loket'), 'Analogi loket/slip');
check(str_contains($body, 'authorize') && str_contains($body, '403'), 'Gloss authorize/403');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check((str_contains($body, 'API Resource') || str_contains($body, 'Resource')) && ! str_contains($body, '/artikel/laravel-api-resource-json'), 'Soft bridge API Resource');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek Laravel');
check(! str_contains($body, 'endpoint'), 'Tanpa endpoint');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'pemanggil') && str_contains($body, 'yang memanggil API'), 'Gloss pemanggil');
check(str_contains($body, 'pengatur kode') || str_contains($body, 'controller'), 'Gloss controller');
check(str_contains($body, 'peminjaman') || str_contains($body, 'pinjam'), 'Domain pinjam');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'"), 'Field EN ada');
check(str_contains($bodyEn, '#66 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN ada');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Install-from-scratch'), 'Tools-first EN');
check(! str_contains($body, 'supaya UI '), 'Tanpa jargon UI');
check(substr_count($body, '<a ') - substr_count($body, '</a>') === 0, 'Thin anchor balance');
$noSvg = preg_replace('/<svg\b.*?<\/svg>/is', '', $body) ?? $body;
$noA = preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $noSvg) ?? $noSvg;
check(! preg_match('/(?<![\w\/"#>])#6[0-9](?!\s*\(ini\))/', strip_tags($noA)), 'Thin/bare numbered = 0');
check(str_contains($body, 'satu terminal') || str_contains($body, 'satu terminal sebenarnya cukup'), 'Satu terminal cukup');
check(str_contains($body, 'terminal kedua'), 'Terminal kedua dijelaskan');
check(str_contains($bodyEn, 'curl.exe'), 'EN curl.exe');
check(str_contains($body, 'php izin-cek.php') && str_contains($body, 'demo('), '3-tier izin-cek → demo');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
