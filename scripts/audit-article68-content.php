<?php

/**
 * Content / checklist audit #68 Feature Test API.
 * Usage: php scripts/audit-article68-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article68Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #68 ===\n\n";

$ref = new ReflectionClass(Article68Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article68Seeder.php');
$slug = 'laravel-feature-test-api';
$prevSlug = 'laravel-api-resource-json';

check(str_contains($body, '#68 (ini)'), 'Self-ref #68 (ini)');
check(! preg_match('/(?<![\w\/"#>])#69(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #69');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#67(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #67 di prosa');
check(! preg_match('/(?<![\w\/"#>])#66(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #66 di prosa');
check(str_contains($body, '/artikel/'.$prevSlug), 'Link #67');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_feature_test_api_demo.php'), 'File contoh');
check(str_contains($body, 'uji-cek.php'), 'Mid file uji-cek');
check(str_contains($body, 'curl.exe'), 'curl.exe Windows awam');
check(str_contains($body, '404') && str_contains($body, 'rute pinjam mungkin belum'), 'Gloss curl 404 rute belum');
check(str_contains($body, 'cara menguji bagian ini') && str_contains($body, 'laravel_feature_test_api_demo.php'), 'Cara uji demo lengkap');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel68testArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-68'), 'Route hook');
check(str_contains($body, '5/7'), 'Progress 5/7');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'Persiapan') && str_contains($body, 'notepad tests\Feature\PeminjamanResourceTest.php'), 'Tools-first ID');
check(str_contains($body, 'tests\Feature') || str_contains($body, 'tests\\Feature'), 'Path Feature');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'Belum perlu hardlink') && ! str_contains($body, 'soft, belum hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article68.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article68-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article68-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article68-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'PHP biasa') || str_contains($body, 'Kenapa PHP'), 'Narasi PHP dulu');
check(str_contains($body, 'slip pinjam') || str_contains($body, 'loket') || str_contains($body, 'checklist'), 'Analogi loket/slip');
check(str_contains($body, 'assertJson') && str_contains($body, 'assertStatus'), 'Gloss assertJson/assertStatus');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'Rate Limiting') && ! str_contains($body, '/artikel/laravel-rate-limiting-api'), 'Soft bridge Rate Limiting');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek Laravel');
check(! str_contains($body, 'endpoint'), 'Tanpa endpoint');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'Feature Test'), 'Feature Test');
check(str_contains($body, 'peminjaman') || str_contains($body, 'pinjam'), 'Domain pinjam');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'"), 'Field EN ada');
check(str_contains($bodyEn, '#68 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN ada');
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
check(str_contains($bodyEn, 'how to test this part') && str_contains($bodyEn, 'laravel_feature_test_api_demo.php'), 'EN cara uji demo');
check(str_contains($body, 'php uji-cek.php') && str_contains($body, 'demo(') && (str_contains($body, 'php artisan test') || str_contains($body, 'vendor/bin/phpunit')), '3-tier uji-cek → demo → artisan test');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
