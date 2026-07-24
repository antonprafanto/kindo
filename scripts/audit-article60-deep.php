<?php

/**
 * Deep-audit pass-1 #60 — Capstone ramah awam.
 * Usage: php scripts/audit-article60-deep.php
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

echo "=== Deep-audit pass-1 #60 ===\n\n";

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article60Seeder.php');
$plain = trim(preg_replace('/\s+/u', ' ', strip_tags($body)) ?? '');
$words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.(isset($m[1]) ? mb_strlen($m[1]) : 0).')');
check(str_contains($body, 'bukti_masuk') && str_contains($body, '401') && str_contains($body, '422'), 'Bukti + 401 + 422');
check(str_contains($body, 'Kenapa belum langsung') || str_contains($body, 'PHP biasa'), 'Fondasi PHP dulu');
check(str_contains($body, 'Seri 4') && str_contains($body, '#60 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel');
check(substr_count($body, '/artikel/laravel-auth-api-dasar') >= 2, '≥2 link #59');
check(! preg_match('/(?<![\w\/"#>])#61(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #61');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#59(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #59');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'laravel_capstone_perpustakaan_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'capstone-api-perpustakaan-laravel'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*true/", $src) === 1, 'is_featured true');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle60'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 60 via deploy hook (required)'), 'CI #60 required');
check(! preg_match('/Publish article 60 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #60 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php'), 'capstone-api-perpustakaan-laravel'), '#59 hardlink #60');
check(str_contains($body, '8/8 Capstone Laravel'), 'Progress 8/8');
check(str_contains($body, 'stack Laravel') || str_contains($body, '5/5'), 'Framing stack Laravel');
check(str_contains($body, 'Arti awam') || str_contains($body, 'bukti masuk'), 'Gloss awam');
check(str_contains($body, 'Laravel lanjutan') || str_contains($body, 'laravel-crud-api-buku-ubah-hapus'), 'Jembatan Laravel lanjutan / #61');
check(str_contains($body, 'laravel-crud-api-buku-ubah-hapus'), 'Hardlink #61');
check(str_contains($body, 'loket') || str_contains($body, 'perpustakaan'), 'Analogi loket/perpustakaan');
check(str_contains($body, 'Belum diizinkan'), 'Gloss Belum diizinkan');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Spesifikasi fitur'), 'Spesifikasi');
check(str_contains($body, 'Indeks Seri 4'), 'Indeks');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, '422') && str_contains($body, '401'), 'Bedakan 401 vs 422');
check(str_contains($body, 'StoreBookRequest') && str_contains($body, 'Form Request'), 'Gloss StoreBookRequest');
check(str_contains($body, 'JsonResponse') && str_contains($body, 'tipe jawaban'), 'Gloss JsonResponse');
check(str_contains($body, 'BukuService') && str_contains($body, 'pekerja'), 'Gloss Service awam');
check(str_contains($body, 'auth:sanctum') && str_contains($body, 'pemeriksa'), 'Gloss auth:sanctum');
check(str_contains($body, 'strict_types') && str_contains($body, 'tipe'), 'Gloss strict_types');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Link #56');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Link #57');
check(str_contains($body, '/artikel/laravel-controller-service-eloquent'), 'Link #58');
check(str_contains($body, 'katalog publik') || str_contains($body, 'Katalog publik'), 'Katalog publik framing');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek (bukan project)');
check(str_contains($body, 'pemanggil') && str_contains($body, 'yang memanggil API'), 'Gloss pemanggil');
check(str_contains($body, 'Cek login'), 'SVG Cek login (bukan Auth mentah)');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(! str_contains($body, 'repo Laravel') && str_contains($body, 'folder proyek'), 'FAQ folder proyek');
check(str_contains($body, 'validated()') && str_contains($body, 'sudah lolos'), 'Gloss validated()');
check(str_contains($body, 'menyiapkan layanan otomatis') || str_contains($body, 'tidak perlu'), 'Gloss DI konstruktor');
check(str_contains($body, 'pemeriksa pintu (middleware)'), 'KU middleware digloss');
check(str_contains($body, 'dijaga pemeriksa pintu') && str_contains($body, 'middleware'), 'Awam store: pemeriksa pintu dulu');
check(str_contains($body, 'Sanctum') && str_contains($body, 'paket Laravel') && str_contains($body, 'bukti masuk API'), 'FAQ Sanctum digloss');
check(str_contains($body, 'bukti masuk API') || ! str_contains($body, 'API token'), 'FAQ tanpa API token mentah');

echo "\n=== Deep-audit pass-1 #60: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH LIVE Capstone — hardlink #59 terkunci. STOP AUDIT → oke deploy hanya untuk resync/bug.\n";
}
exit($failed > 0 ? 1 : 0);
