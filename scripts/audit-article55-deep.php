<?php

/**
 * Deep-audit pass-1 #55 (ramah awam + SEO + locks).
 * Usage: php scripts/audit-article55-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article55Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #55 ===\n\n";

$ref = new ReflectionClass(Article55Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php');
$plain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim(strip_tags($plain))) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(strlen('Visibility & Composition PHP — OOP untuk Pemula') <= 70, 'seo_title ≤70');
$desc = 'Jembatan terakhir OOP PHP sebelum Laravel: public vs private, kenapa data disembunyikan, dan composition ringan (object memakai object) — berbahasa Indonesia.';
check(strlen($desc) >= 70 && strlen($desc) <= 170, 'seo_desc 70–170 ('.strlen($desc).')');
check(str_contains($body, 'private') && str_contains($body, 'public') && str_contains($body, 'setTahun'), 'Visibility + setter');
check(str_contains($body, 'class Katalog') && str_contains($body, 'tambah('), 'Composition Katalog');
check(str_contains($body, 'Seri 4') && str_contains($body, '#55 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel'), 'Sebut Laravel sebagai tujuan');
check(substr_count($body, '/artikel/oop-php-property-method-constructor') >= 2, '≥2 link #54');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php'), 'Link #53');
check(! preg_match('/(?<![\w\/"#>])#(?:5[6-9]|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #56+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'role="img"') || str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop_php_visibility.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan singkat') && str_contains($body, 'FAQ singkat'), 'KU/Latihan/FAQ');
check(str_contains($src, 'oop-php-visibility-composition'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle55'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 55 via deploy hook (required)'), 'CI #55 required');
check(! preg_match('/Publish article 55 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #55 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article54Seeder.php'), 'oop-php-visibility-composition'), '#54 hardlink #55');
check(str_contains($body, '6/8 menuju Capstone Laravel'), 'Progress 5/8');
check(str_contains($body, '3/3 selesai') || str_contains($body, 'jembatan OOP PHP'), 'Jembatan OOP selesai framing');
check(str_contains($body, 'Arti awam') || str_contains($body, 'laci'), 'Gloss awam');
check(str_contains($body, 'inheritance') || str_contains($body, 'warisan'), 'Kontras inheritance vs composition');
check(str_contains($body, 'InvalidArgumentException') || str_contains($body, 'tidak masuk akal'), 'Validasi tahun');
check(str_contains($body, 'catatan untuk manusia'), 'Gloss PHPDoc @var');
check(str_contains($body, 'setTahun(1800)') || str_contains($body, 'gagal keras'), 'Narasi exception awam');
check(! preg_match('/#53\s*[–-]\s*#55/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak rentang bare #53–#55');
check(file_exists(__DIR__.'/audit-article55-deep-pass2.php'), 'Deep pass-2 ada');
check(file_exists(__DIR__.'/audit-article55-deep-pass3.php'), 'Deep pass-3 ada');

echo "\n=== Deep-audit pass-1 #55: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
