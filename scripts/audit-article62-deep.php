<?php

/**
 * Deep-audit pass-1 #62 — Relasi Eloquent ramah awam.
 * Usage: php scripts/audit-article62-deep.php
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

echo "=== Deep-audit pass-1 #62 ===\n\n";

$ref = new ReflectionClass(Article62Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article62Seeder.php');
$plain = trim(preg_replace('/\s+/u', ' ', strip_tags($body)) ?? '');
$words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.(isset($m[1]) ? mb_strlen($m[1]) : 0).')');
check(str_contains($body, 'hasMany') && str_contains($body, 'belongsTo'), 'hasMany + belongsTo');
check(str_contains($body, 'Kenapa PHP') || str_contains($body, 'PHP biasa'), 'Fondasi PHP dulu');
check(str_contains($body, 'Seri 5') && str_contains($body, '#62 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel');
check(substr_count($body, '/artikel/laravel-crud-api-buku-ubah-hapus') >= 2, '≥2 link #61');
check(str_contains($body, '/artikel/capstone-api-perpustakaan-laravel'), 'Link #60');
check(! preg_match('/(?<![\w\/"#>])#62(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #62 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#63(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #63');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#61(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #61');
check(! preg_match('/(?<![\w\/"#>])#60(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #60');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'laravel_eloquent_relasi_peminjaman_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-eloquent-relasi-peminjaman'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle62'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 62 via deploy hook (required)'), 'CI #62 required');
check(! preg_match('/Publish article 62 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #62 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article61Seeder.php'), 'laravel-eloquent-relasi-peminjaman'), '#61 hardlink #62');
check(str_contains($body, '2/8 Laravel Lanjutan'), 'Progress 2/8');
check(str_contains($body, 'Laravel Lanjutan') || str_contains($body, 'Framework-based'), 'Framing Seri 5');
check(str_contains($body, 'Arti awam') || str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'Pagination') || str_contains($body, 'pagination'), 'Jembatan soft ke #63');
check(str_contains($body, 'loket') || str_contains($body, 'rak') || str_contains($body, 'perpustakaan'), 'Analogi rak/loket');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Spesifikasi fitur'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'strict_types') && str_contains($body, 'tipe'), 'Gloss strict_types');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek (bukan project)');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, 'Anggota tidak ketemu'), 'Gloss 404 anggota');
check(str_contains($body, 'anggota_id') && str_contains($body, 'buku_id'), 'Kunci asing framing');
check(str_contains($body, 'hasMany') && str_contains($body, 'punya banyak'), 'Gloss hasMany awam');
check(str_contains($body, 'belongsTo') && (str_contains($body, 'milik satu') || str_contains($body, 'milik')), 'Gloss belongsTo awam');
check(str_contains($body, 'N+1') || str_contains($body, 'pagination'), 'Soft N+1 / pagination');
check(str_contains($body, 'satu-satu di dalam loop') || str_contains($body, 'satu per satu'), 'KU N+1 digloss');
check(str_contains($body, 'Migrasi (skrip') || str_contains($body, 'skrip buat'), 'Gloss migrasi');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(str_contains($body, 'model') && str_contains($body, 'mewakili satu tabel'), 'Gloss model');
check(str_contains($body, 'pengatur kode') && str_contains($body, 'pekerja'), 'Gloss controller/service');
check(! str_contains($body, '/artikel/laravel-pagination'), 'Tanpa hardlink #63 unpublished');

echo "\n=== Deep-audit pass-1 #62: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH LIVE #62 — hardlink #61 terkunci. STOP AUDIT → oke deploy hanya untuk resync/bug.\n";
}
exit($failed > 0 ? 1 : 0);
