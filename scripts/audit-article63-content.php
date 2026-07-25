<?php

/**
 * Content / checklist audit #63.
 * Usage: php scripts/audit-article63-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article63Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #63 ===\n\n";

$ref = new ReflectionClass(Article63Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article63Seeder.php');
$slug = 'laravel-pagination-filter-pencarian';

check(str_contains($body, '#63 (ini)'), 'Self-ref #63 (ini)');
check(! preg_match('/(?<![\w\/"#>])#64(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #64');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#62(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #62 di prosa');
check(! preg_match('/(?<![\w\/"#>])#61(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #61 di prosa');
check(str_contains($body, '/artikel/laravel-eloquent-relasi-peminjaman'), 'Link #62');
check(str_contains($body, '/artikel/laravel-crud-api-buku-ubah-hapus'), 'Link #61');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_pagination_filter_pencarian_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel63pageArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-63'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '3/8 Laravel Lanjutan'), 'Progress 3/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, '422'), 'Status 422');
check(! str_contains($body, 'TODO'), 'Tanpa suara editor');
check(file_exists(__DIR__.'/audit-article63.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article63-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article63-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article63-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'PHP biasa') || str_contains($body, 'Kenapa PHP'), 'Narasi PHP dulu');
check(str_contains($body, 'loket') || str_contains($body, 'perpustakaan'), 'Analogi loket/perpustakaan');
check(str_contains($body, 'paginate') && str_contains($body, 'potong'), 'Gloss paginate');
check(str_contains($body, 'like') && str_contains($body, 'mengandung'), 'Gloss like');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Laravel 11+'), 'Versi Laravel awam');
check(str_contains($body, 'Policy') || str_contains($body, 'policy'), 'Soft bridge Policy');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek Laravel');
check(! str_contains($body, 'endpoint'), 'Tanpa endpoint');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'pemanggil') && str_contains($body, 'yang memanggil API'), 'Gloss pemanggil');
check(str_contains($body, 'pengatur kode') && str_contains($body, 'pekerja'), 'Gloss controller/service');
check(str_contains($body, 'saring dulu') || str_contains($body, 'Saring dulu'), 'Urutan saring dulu');
check(str_contains($body, 'info ringkas') || str_contains($body, 'Kirim info'), 'Gloss meta→info');
check(str_contains($body, 'aturan izin') && str_contains($body, 'policy'), 'Soft Policy awam-first');
check(! str_contains($body, 'authorization policy'), 'Tanpa authorization mentah');
check(! str_contains($body, 'supaya UI '), 'Tanpa jargon UI');
check(substr_count($body, '<a ') - substr_count($body, '</a>') === 0, 'Thin anchor balance');
$noSvg = preg_replace('/<svg\b.*?<\/svg>/is', '', $body) ?? $body;
$noA = preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $noSvg) ?? $noSvg;
check(! preg_match('/(?<![\w\/"#>])#6[0-9](?!\s*\(ini\))/', strip_tags($noA)), 'Thin/bare numbered = 0');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
