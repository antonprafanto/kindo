<?php

/**
 * Deep-audit pass-1 #62 — Capstone API Perpustakaan ramah awam.
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
$plain = strip_tags($body);
$words = str_word_count(strip_tags(html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8')));

check($words >= 550, 'Prosa ≥550 kata ('.$words.')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 3, '≥3 blok PHP');
check(strlen('Capstone API Perpustakaan Laravel: Baca, Login, Tambah') <= 70, 'seo_title ≤70');
$seoDesc = 'Seri 4 #62 Capstone: gabungkan baca katalog, login Sanctum, dan tambah buku ber-kartu — ramah awam.';
$seoLen = strlen($seoDesc);
check($seoLen >= 70 && $seoLen <= 170, 'seo_desc 70–170 ('.$seoLen.')');
check(str_contains($body, 'BukuController'), 'BukuController');
check(str_contains($body, 'auth:sanctum') && str_contains($body, 'POST /api/buku'), 'POST ber-auth');
check(str_contains($body, 'Bearer') || str_contains($body, 'token'), 'Token/Bearer');
check(str_contains($body, 'Capstone') || str_contains($body, 'baca'), 'Fondasi Capstone');
check(str_contains($body, 'Seri 4') && str_contains($body, '#62 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel');
check(str_contains($body, 'PHP 8.3'), 'Syarat PHP 8.3');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin 11+ usang');
check(substr_count($body, '/artikel/laravel-auth-api-dasar') >= 2, '≥2 link #61');
$withoutLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '';
check(preg_match_all('/#62(?!\s*\(ini\))/', $withoutLinks) === 0, 'Tidak bare #62 (kecuali ini)');
check(preg_match_all('/#63(?!\s*\(ini\))/', $withoutLinks) === 0, 'Tidak bare #63');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') || str_contains($body, 'role="img"'), 'SVG a11y');
check(str_contains($body, 'laravel_capstone_api_perpustakaan_demo.php') && str_contains($body, 'demo()'), 'File + demo');
check(str_contains($body, 'Pola Dasar') && str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'capstone-api-perpustakaan-laravel'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
check(str_contains($deploy, 'capstone-api-perpustakaan-laravel'), 'Hook');
check(str_contains($yml, 'capstone-api-perpustakaan-laravel'), 'CI slug');
check(preg_match('/Publish article 62 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml) !== 1, 'CI #62 required (tanpa continue-on-error)');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article61Seeder.php'), 'capstone-api-perpustakaan-laravel'), '#61 hardlink #62');
check(str_contains($deploy, 'Article 62 backlink #61') || str_contains($deploy, 'backlink missing on #61'), 'Hook hardlink #61 aktif');
check(str_contains($body, '7/8'), 'Progress 7/8');
check(str_contains($body, 'Web Lanjut v2') || str_contains($body, 'jalur Laravel'), 'Framing Seri 4 v2');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'ubah') || str_contains($body, 'hapus'), 'Soft bridge #63');
check(! str_contains($body, 'Pin ') && ! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(str_contains($body, 'strict_types'), 'Gloss strict_types');
check(str_contains($body, 'proyek') || str_contains($body, 'Proyek'), 'Proyek (bukan project)');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'terminal'), 'Petunjuk tools awam');
check(str_contains($body, 'terminal kedua'), 'Terminal kedua saat serve');
check(str_contains($body, 'Explorer') && str_contains($body, 'curl.exe'), 'Explorer + curl');
check(str_contains($body, 'Opsi C') || str_contains($body, 'Postman'), 'Opsi C / Postman');
check(str_contains($body, 'salin token') || str_contains($body, 'Salin token') || str_contains($body, 'Token yang mana'), 'Tip/FAQ salin token');
check(! str_contains($body, '%%{http_code}'), 'Tanpa %%{http_code}');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor hardlink');
check(preg_match_all('/#61(?!\s*\(ini\))/', $withoutLinks) === 0, 'Tanpa bare #61');

echo "\n=== Deep-audit pass-1 #62: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: LIVE #62 — hardlink #61 aktif · CI required. Next: kickoff #63.\n";
}
exit($failed > 0 ? 1 : 0);
