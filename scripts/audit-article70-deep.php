<?php

/**
 * Deep-audit pass-1 #70 — Capstone Pinjam & Kembalikan ramah awam.
 * Usage: php scripts/audit-article70-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article70Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #70 ===\n\n";

$ref = new ReflectionClass(Article70Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article70Seeder.php');
$plain = trim(preg_replace('/\s+/u', ' ', strip_tags($body)) ?? '');
$words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.(isset($m[1]) ? mb_strlen($m[1]) : 0).')');
check(str_contains($body, 'authorize') && str_contains($body, 'throttle:pinjam') && str_contains($body, 'assertJsonStructure'), 'Policy + throttle + test refs');
check(str_contains($body, 'Kenapa PHP') || str_contains($body, 'PHP biasa'), 'Fondasi PHP dulu');
check(str_contains($body, 'Seri 5') && str_contains($body, '#70 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel');
check(substr_count($body, '/artikel/laravel-rate-limiting-api') >= 1, '≥1 link #69');
check(substr_count($body, '/artikel/laravel-eloquent-relasi-peminjaman') >= 1, '≥1 link #64');
check(! preg_match('/(?<![\w\/"#>])#70(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #70 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#71(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #71');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#69(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #69');
check(! preg_match('/(?<![\w\/"#>])#68(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #68');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'capstone_pinjam_kembali_laravel_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'capstone-pinjam-kembali-laravel'), 'Slug');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'"), 'Field EN');
check(str_contains($bodyEn, '#70 (this article)') && str_contains($bodyEn, 'Beginner:'), 'EN body dasar');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Preparation'), 'EN tools-first');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle70'), 'Hook');
$deployYml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
check(str_contains($deployYml, 'publish-article-70'), 'CI step #70 ada');
check(str_contains($deployYml, 'Publish article 70 via deploy hook (required)'), 'CI #70 required');
check(str_contains($body, '7/7'), 'Progress 7/7 tamat');
check(str_contains($body, 'tamat') || str_contains($body, 'penutup'), 'Framing Seri 5 tamat');
check(str_contains($body, 'Arti awam') || str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'Piranti Bergerak') && ! preg_match('/\/artikel\/[^"]*piranti/i', $body), 'Jembatan soft Piranti Bergerak');
check(str_contains($bodyEn, 'Mobile Devices') && ! preg_match('/\/artikel\/[^"]*mobile/i', $bodyEn), 'EN soft Mobile Devices');
check(str_contains($body, 'petugas') || str_contains($body, 'perpustakaan'), 'Analogi petugas');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Spesifikasi fitur'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'strict_types'), 'Gloss strict_types ada di demo');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek (bukan project)');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, 'Persiapan') && str_contains($body, 'satu terminal'), 'Tools-first + satu terminal');
check(str_contains($body, 'alur-cek.php'), 'Mid file alur-cek');
check(! str_contains($body, 'Belum perlu hardlink') && ! str_contains($body, 'soft, belum hardlink'), 'Tanpa suara editor hardlink');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');

echo "\n=== Deep-audit pass-1 #70: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: #70 capstone ID+EN tools-first hijau — siap LIVE.\n";
}
exit($failed > 0 ? 1 : 0);
