<?php

/**
 * Deep-audit pass-1 #66 — authorization policy ramah awam.
 * Usage: php scripts/audit-article66-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article66Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #66 ===\n\n";

$ref = new ReflectionClass(Article66Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article66Seeder.php');
$plain = trim(preg_replace('/\s+/u', ' ', strip_tags($body)) ?? '');
$words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];
$deployYml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.(isset($m[1]) ? mb_strlen($m[1]) : 0).')');
check(str_contains($body, 'authorize') && str_contains($body, 'PeminjamanPolicy'), 'authorize + PeminjamanPolicy');
check(str_contains($body, 'Kenapa PHP') || str_contains($body, 'PHP biasa'), 'Fondasi PHP dulu');
check(str_contains($body, 'Seri 5') && str_contains($body, '#66 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel');
check(substr_count($body, '/artikel/laravel-pagination-filter-pencarian') >= 1, '≥1 link #65');
check(! preg_match('/(?<![\w\/"#>])#66(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #66 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#67(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #67');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#65(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #65');
check(! preg_match('/(?<![\w\/"#>])#64(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #64');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'laravel_policy_otorisasi_api_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-policy-otorisasi-api'), 'Slug');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'"), 'Field EN');
check(str_contains($bodyEn, '#66 (this article)') && str_contains($bodyEn, 'Beginner:'), 'EN body dasar');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Preparation'), 'EN tools-first');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle66'), 'Hook');
check(str_contains($deployYml, 'publish-article-66'), 'CI step #66 ada');
check(! preg_match('/Publish article 66 via deploy hook \(WIP\)\s*\n\s*continue-on-error:\s*true/u', $deployYml) && str_contains($deployYml, 'Publish article 66 via deploy hook (required)'), 'CI #66 required');
check(str_contains($body, '3/7'), 'Progress 3/7');
check(str_contains($body, 'Laravel Lanjutan') || str_contains($body, 'Framework-based'), 'Framing Seri 5');
check(str_contains($body, 'Arti awam') || str_contains($body, 'Awam:'), 'Gloss awam');
check((str_contains($body, 'API Resource') || str_contains($body, 'Resource')) && ! str_contains($body, '/artikel/laravel-api-resource-json'), 'Jembatan soft ke API Resource');
check(str_contains($body, 'slip pinjam') || str_contains($body, 'loket'), 'Analogi loket');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Spesifikasi fitur'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'strict_types'), 'Gloss strict_types ada di demo');
check(str_contains($body, 'proyek') && ! str_contains($body, 'project '), 'Proyek (bukan project)');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, 'pemanggil') && str_contains($body, 'yang memanggil API'), 'Gloss pemanggil');
check(str_contains($body, 'Persiapan') && str_contains($body, 'satu terminal'), 'Tools-first + satu terminal');
check(str_contains($body, 'izin-cek.php'), 'Mid file izin-cek');
check(! str_contains($body, 'Belum perlu hardlink') && ! str_contains($body, 'soft, belum hardlink'), 'Tanpa suara editor hardlink');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');

echo "\n=== Deep-audit pass-1 #66: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: #66 policy ID+EN tools-first hijau — siap LIVE.\n";
}
exit($failed > 0 ? 1 : 0);
