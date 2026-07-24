<?php

/**
 * Content / checklist audit #61.
 * Usage: php scripts/audit-article61-content.php
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

echo "=== Content / checklist audit #61 ===\n\n";

$ref = new ReflectionClass(Article61Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article61Seeder.php');
$slug = 'laravel-crud-api-buku-ubah-hapus';

check(str_contains($body, '#61 (ini)'), 'Self-ref #61 (ini)');
check(! preg_match('/(?<![\w\/"#>])#62(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #62');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#60(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #60 di prosa');
check(str_contains($body, '/artikel/capstone-api-perpustakaan-laravel'), 'Link #60');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_crud_buku_ubah_hapus_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel61crudArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-61'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '1/8 Laravel Lanjutan'), 'Progress 1/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, '401') && str_contains($body, '404'), 'Status 401+404');
check(! str_contains($body, 'TODO'), 'Tanpa suara editor');
check(file_exists(__DIR__.'/audit-article61.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article61-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article61-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article61-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'PHP biasa') || str_contains($body, 'Kenapa PHP'), 'Narasi PHP dulu');
check(str_contains($body, 'rak') || str_contains($body, 'perpustakaan'), 'Analogi rak/perpustakaan');
check(str_contains($body, 'bukti masuk'), 'Gloss bukti masuk');
check(str_contains($body, 'Belum diizinkan'), 'Gloss Belum diizinkan');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Laravel 11+'), 'Versi Laravel awam');
check(str_contains($body, 'Relasi Eloquent'), 'Soft bridge Relasi');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(str_contains($body, 'middleware') && str_contains($body, 'pemeriksa'), 'Gloss middleware');
check(str_contains($body, 'dijaga pemeriksa pintu'), 'Awam update awam-first');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'UpdateBookRequest') && str_contains($body, 'penjaga'), 'Gloss UpdateBookRequest');
check(str_contains($body, 'JsonResponse') && str_contains($body, 'tipe jawaban'), 'Gloss JsonResponse');
check(str_contains($body, 'BukuService') && str_contains($body, 'pekerja'), 'Service framing');
check(str_contains($body, 'auth:sanctum'), 'Route auth cuplikan');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek Laravel');
check(str_contains($body, 'pemanggil') && str_contains($body, 'yang memanggil API'), 'Gloss pemanggil');
check(str_contains($body, 'Cek login'), 'SVG Cek login');
check(str_contains($body, 'validated()') && str_contains($body, 'sudah lolos'), 'Gloss validated()');
check(! str_contains($body, 'endpoint'), 'Tanpa endpoint');
$thin = preg_match_all('/<a[^>]*>\s*#\d+\s*<\/a>/', $body);
check($thin === 0, 'Thin anchor = 0');
check(str_contains($body, 'destroy') && str_contains($body, 'buang data'), 'Gloss destroy');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'belum dihapus di service') && str_contains($body, 'unset'), 'KU unset digloss');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
