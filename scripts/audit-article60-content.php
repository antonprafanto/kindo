<?php

/**
 * Content / checklist audit #60 — Capstone.
 * Usage: php scripts/audit-article60-content.php
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

echo "=== Content / checklist audit #60 ===\n\n";

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article60Seeder.php');

check(str_contains($body, '#60 (ini)'), 'Self-ref #60 (ini)');
check(! preg_match('/(?<![\w\/"#>])#61(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #61');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#59(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #59 di prosa');
check(str_contains($body, '/artikel/laravel-auth-api-dasar'), 'Link #59');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_capstone_perpustakaan_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan singkat'), 'Latihan');
check(str_contains($body, 'FAQ singkat'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel60capArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*true/", $src) === 1, 'is_featured true');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'capstone-api-perpustakaan-laravel'), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-60'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'capstone-api-perpustakaan-laravel'), 'CI slug');
check(str_contains($body, '8/8 Capstone Laravel'), 'Progress 8/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Arti awam'), 'Gloss awam');
check(str_contains($body, '401') && str_contains($body, '422'), 'Status 401+422');
check(! str_contains($body, 'tanpa hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php'), 'capstone-api-perpustakaan-laravel'), '#59 hardlink #60');
check(file_exists(__DIR__.'/audit-article60.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article60-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article60-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article60-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'Kenapa belum langsung') || str_contains($body, 'PHP biasa'), 'Narasi PHP dulu');
check(str_contains($body, 'loket') || str_contains($body, 'perpustakaan'), 'Analogi loket/perpustakaan');
check(str_contains($body, 'bukti masuk'), 'Gloss bukti masuk');
check(str_contains($body, 'Belum diizinkan'), 'Gloss Belum diizinkan');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Pakai') && str_contains($body, 'Laravel 11+'), 'Versi Laravel awam');
check(str_contains($body, 'Laravel Lanjutan') || str_contains($body, 'laravel-crud-api-buku-ubah-hapus'), 'Bridge Laravel lanjutan / #61');
check(str_contains($body, 'laravel-crud-api-buku-ubah-hapus'), 'Hardlink #61');
check(str_contains($body, 'Spesifikasi fitur'), 'Spesifikasi Capstone');
check(str_contains($body, 'Indeks Seri 4'), 'Indeks lengkap');
check(str_contains($body, 'middleware') && str_contains($body, 'pemeriksa'), 'Gloss middleware');
check(str_contains($body, 'dijaga pemeriksa pintu'), 'Awam store awam-first');
check(str_contains($body, 'paket Laravel') && str_contains($body, 'Sanctum'), 'FAQ Sanctum digloss');
check(! str_contains($body, '@param') && ! str_contains($body, '@return'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized'), 'Tanpa jargon Unauthorized');
check(! str_contains($body, 'JWT'), 'Tanpa JWT');
check(str_contains($body, 'StoreBookRequest') && str_contains($body, 'Form Request'), 'Gloss StoreBookRequest');
check(str_contains($body, 'JsonResponse') && str_contains($body, 'tipe jawaban'), 'Gloss JsonResponse');
check(str_contains($body, 'BukuService'), 'Service framing');
check(str_contains($body, 'auth:sanctum'), 'Route auth cuplikan');
check(str_contains($body, 'proyek Laravel'), 'Proyek Laravel');
check(str_contains($body, 'yang memanggil API'), 'Gloss pemanggil');
check(str_contains($body, 'Cek login'), 'SVG Cek login');
check(str_contains($body, 'validated()'), 'Gloss validated()');
check(! str_contains($body, 'endpoint'), 'Tanpa endpoint');

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check($thin === 0, 'Thin anchor = 0');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
