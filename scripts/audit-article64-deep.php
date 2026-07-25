<?php

/**
 * Deep-audit pass-1 #64 — Policy/aturan izin ramah awam.
 * Usage: php scripts/audit-article64-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article64Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #64 ===\n\n";

$ref = new ReflectionClass(Article64Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article64Seeder.php');
$plain = trim(preg_replace('/\s+/u', ' ', strip_tags($body)) ?? '');
$words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.(isset($m[1]) ? mb_strlen($m[1]) : 0).')');
check(str_contains($body, 'authorize') && str_contains($body, 'Policy'), 'authorize + Policy');
check(str_contains($body, 'Kenapa PHP') || str_contains($body, 'PHP biasa'), 'Fondasi PHP dulu');
check(str_contains($body, 'Seri 5') && str_contains($body, '#64 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel');
check(substr_count($body, '/artikel/laravel-pagination-filter-pencarian') >= 2, '≥2 link #63');
check(str_contains($body, '/artikel/laravel-eloquent-relasi-peminjaman'), 'Link #62');
check(! preg_match('/(?<![\w\/"#>])#64(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #64 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#65(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #65');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#63(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #63');
check(! preg_match('/(?<![\w\/"#>])#62(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #62');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'laravel_policy_otorisasi_api_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-policy-otorisasi-api'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle64'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 64 via deploy hook (required)'), 'CI #64 required');
check(! preg_match('/Publish article 64 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #64 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article63Seeder.php'), 'laravel-policy-otorisasi-api'), '#63 hardlink #64');
check(str_contains($body, '4/8 Laravel Lanjutan'), 'Progress 4/8');
check(str_contains($body, 'Laravel Lanjutan') || str_contains($body, 'Framework-based'), 'Framing Seri 5');
check(str_contains($body, 'Arti awam') || str_contains($body, 'Awam:'), 'Gloss awam');
check((str_contains($body, 'API Resource') || str_contains($body, 'resource JSON')) && ! str_contains($body, '/artikel/laravel-api-resource'), 'Jembatan soft ke #65');
check(str_contains($body, 'kartu anggota') || str_contains($body, 'perpustakaan'), 'Analogi kartu');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Spesifikasi fitur'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'strict_types') && str_contains($body, 'tipe'), 'Gloss strict_types');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek (bukan project)');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, 'Tidak punya izin'), 'Gloss 403 awam');
check(str_contains($body, 'pemanggil') && str_contains($body, 'yang memanggil API'), 'Gloss pemanggil');
check(str_contains($body, 'aturan izin') && str_contains($body, 'policy'), 'Policy awam-first');
check(! str_contains($body, 'authorization policy'), 'Tanpa authorization mentah');
check(! str_contains($body, '/artikel/laravel-api-resource-json'), 'Tanpa hardlink #65 unpublished');
check(! str_contains($body, 'Belum perlu hardlink') && ! str_contains($body, 'soft, belum hardlink'), 'Tanpa suara editor hardlink');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');

echo "\n=== Deep-audit pass-1 #64: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH LIVE #64 — hardlink #63 terkunci. STOP AUDIT → oke deploy hanya untuk resync/bug.\n";
}
exit($failed > 0 ? 1 : 0);
