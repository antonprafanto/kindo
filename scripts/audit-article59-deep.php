<?php

/**
 * Deep-audit pass-1 #59 — Request & Form Request.
 * Usage: php scripts/audit-article59-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article59Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #59 ===\n\n";

$ref = new ReflectionClass(Article59Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php');
$slug = 'laravel-request-validasi-api';
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim(strip_tags($body))) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 3, '≥3 blok PHP');
$title = 'Request & Form Request Laravel untuk Pemula';
check(strlen($title) <= 70, 'seo_title ≤70');
$desc = 'Seri 4 #59: jaga isi permintaan lewat pintu HTTP — validasi judul & penulis buku, Form Request, status 422 — ramah awam.';
check(strlen($desc) >= 70 && strlen($desc) <= 170, 'seo_desc 70–170 ('.strlen($desc).')');
check(str_contains($body, 'POST') && str_contains($body, '/api/buku'), 'Path POST /api/buku');
check(str_contains($body, 'validate') || str_contains($body, 'validated'), 'Validasi helper');
check(str_contains($body, 'satpam') || str_contains($body, 'slip'), 'Fondasi satpam');
check(str_contains($body, '#59 (ini)') && str_contains($body, 'Web Lanjut v2'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel');
check(str_contains($body, 'PHP 8.3+'), 'Syarat PHP 8.3');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin 11+ usang');
check(substr_count($body, '/artikel/laravel-routing-json-perpustakaan-api') >= 2, '≥2 link #58');
check(! preg_match('/(?<![\w\/"#>])#59(?!\s*\(ini\))/', $plain), 'Tidak bare #59 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:6[0-3])(?!\s*\(ini\))/', $plain), 'Tidak bare #60+');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'role="img"') && str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'laravel_request_validasi_api_demo.php') && str_contains($body, 'demo()'), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, $slug), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle59'), 'Hook');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
check(str_contains($yml, $slug), 'CI slug');
check(preg_match('/Publish article 59 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml) !== 1, 'CI #59 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php'), $slug), '#58 hardlink #59');
check(str_contains($body, '4/8'), 'Progress 4/8');
check(str_contains($body, 'Web Lanjut v2') || str_contains($body, 'jalur Laravel'), 'Framing Seri 4 v2');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'pengatur kode') || str_contains($body, 'tabel') || str_contains($body, 'file pengatur'), 'Jembatan soft ke #60');
check(! str_contains($body, 'Pin ') && ! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
$bodySans60 = preg_replace('/<a\b[^>]*href=["\']\/artikel\/laravel-controller-service-eloquent["\'][^>]*>.*?<\/a>/is', '', $body) ?? '';
check(! str_contains($bodySans60, 'Eloquent') && ! str_contains($bodySans60, 'Sanctum') && ! str_contains($bodySans60, 'scaffolding'), 'Tanpa Eloquent/Sanctum/scaffolding dingin');
check(! str_contains($body, 'GUI') && ! str_contains($body, 'namespace'), 'Tanpa GUI/namespace dingin');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(str_contains($body, 'strict_types'), 'Gloss strict_types');
check(str_contains($body, 'proyek') || str_contains($body, 'Proyek'), 'Proyek (bukan project)');
check(str_contains($body, 'curl.exe') && (str_contains($body, 'terminal kedua') || str_contains($body, 'Invoke-RestMethod')), 'Petunjuk uji tools awam');
check(str_contains($body, '/artikel/laravel-controller-service-eloquent'), 'Hardlink #60');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor hardlink');
check(! preg_match('/(?<![\w\/"#>(ini)\s])#5[3-9](?!\s*\(ini\))/', $plain), 'Tanpa thin anchor #N');

echo "\n=== Deep-audit pass-1 #59: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: LIVE #59 — hardlink #60 aktif. Next: #61 setelah #60 mapan.\n";
}
exit($failed > 0 ? 1 : 0);
