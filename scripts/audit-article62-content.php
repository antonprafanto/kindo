<?php

/**
 * Content / checklist audit #62.
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

echo "=== Content / checklist audit #62 ===\n\n";

$ref = new ReflectionClass(Article62Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article62Seeder.php');
$slug = 'laravel-eloquent-relasi-peminjaman';

check(str_contains($body, '#62 (ini)'), 'Self-ref #62 (ini)');
check(! preg_match('/(?<![\w\/"#>])#63(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #63');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#61(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #61 di prosa');
check(! preg_match('/(?<![\w\/"#>])#60(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #60 di prosa');
check(str_contains($body, '/artikel/laravel-crud-api-buku-ubah-hapus'), 'Link #61');
check(str_contains($body, '/artikel/capstone-api-perpustakaan-laravel'), 'Link #60');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_eloquent_relasi_peminjaman_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel62relasiArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-62'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '2/8 Laravel Lanjutan'), 'Progress 2/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, '404'), 'Status 404');
check(! str_contains($body, 'TODO'), 'Tanpa suara editor');
check(file_exists(__DIR__.'/audit-article62.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article62-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article62-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article62-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'PHP biasa') || str_contains($body, 'Kenapa PHP'), 'Narasi PHP dulu');
check(str_contains($body, 'rak') || str_contains($body, 'perpustakaan') || str_contains($body, 'loket'), 'Analogi rak/loket');
check(str_contains($body, 'hasMany') && str_contains($body, 'punya banyak'), 'Gloss hasMany');
check(str_contains($body, 'belongsTo') && str_contains($body, 'milik'), 'Gloss belongsTo');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Laravel 11+'), 'Versi Laravel awam');
check(str_contains($body, 'Pagination') || str_contains($body, 'pagination'), 'Soft bridge Pagination');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek Laravel');
check(! str_contains($body, 'endpoint'), 'Tanpa endpoint');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'anggota_id'), 'Framing anggota_id');
check(str_contains($body, 'skrip buat') || str_contains($body, 'Migrasi (skrip'), 'Gloss migrasi');
check(str_contains($body, 'satu-satu') || str_contains($body, 'N+1'), 'KU N+1 digloss');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(str_contains($body, 'mewakili satu tabel'), 'Gloss model');
check(str_contains($body, 'pengatur kode') && str_contains($body, 'pekerja'), 'Gloss controller/service');
check(substr_count($body, '<a ') - substr_count($body, '</a>') === 0, 'Thin anchor balance');
$noSvg = preg_replace('/<svg\b.*?<\/svg>/is', '', $body) ?? $body;
$noA = preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $noSvg) ?? $noSvg;
check(! preg_match('/(?<![\w\/"#>])#6[0-9](?!\s*\(ini\))/', strip_tags($noA)), 'Thin/bare numbered = 0');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
