<?php

/**
 * Audit utama #70 — Capstone Pinjam & Kembalikan (Seri 5 tamat).
 * Usage: php scripts/audit-article70.php
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

$slug = 'capstone-pinjam-kembali-laravel';
$prevSlug = 'laravel-rate-limiting-api';

echo "=== Audit Artikel #70 — Capstone Pinjam & Kembalikan ===\n\n";

$ref = new ReflectionClass(Article70Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article70Seeder.php');

check(str_contains($body, '#70 (ini)'), 'Self-ref');
check(str_contains($body, 'authorize') && str_contains($body, 'PeminjamanResource'), 'Policy + Resource refs');
check(str_contains($body, 'throttle:pinjam'), 'Rate limit ref');
check(str_contains($body, 'laravel70capArrow'), 'SVG marker');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'capstone_pinjam_kembali_laravel_demo.php'), 'File contoh demo');
check(str_contains($body, 'alur-cek.php'), 'Mid file alur-cek');
check(str_contains($body, 'Seri 5'), 'Seri 5');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, "'title_en'") && str_contains($src, "'body_en'") && str_contains($src, 'function bodyEn'), 'Seeder field EN + bodyEn()');
check(str_contains($bodyEn, '#70 (this article)') && str_contains($bodyEn, 'Beginner:'), 'Body EN dasar');
check(str_contains($bodyEn, 'Tools used in this article') && str_contains($bodyEn, 'Preparation'), 'EN tools-first');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/'.$prevSlug), 'Link #69');
check(str_contains($body, '/artikel/laravel-eloquent-relasi-peminjaman'), 'Link #64');
check(str_contains($body, '/artikel/laravel-feature-test-api'), 'Link #68');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#71(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #71');
check(! preg_match('/(?<![\w\/"#>])#69(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #69');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
check(str_contains($routes, 'publish-article-70'), 'Route');
check(str_contains($deploy, 'publishArticle70'), 'DeployController');
check(str_contains($deploy, 'capstone-pinjam-kembali-laravel') && str_contains($deploy, 'Article70Seeder'), 'Hook cek slug');
check(file_exists(__DIR__.'/audit-article70-php.php'), 'audit-article70-php.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($body, '7/7'), 'Progress 7/7 tamat');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(str_contains($body, 'Piranti Bergerak') && ! preg_match('/\/artikel\/[^"]*piranti/i', $body), 'Soft bridge Piranti Bergerak tanpa URL');
check(str_contains($bodyEn, 'Mobile Devices') && ! preg_match('/\/artikel\/[^"]*mobile/i', $bodyEn), 'EN soft bridge Mobile Devices tanpa URL');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'Persiapan') && str_contains($body, 'tests\Feature'), 'Tools-first ID');
check(str_contains($body, 'alur-cek.php') && str_contains($body, 'demo('), '3-tier alur-cek + demo');
check(str_contains($body, 'curl.exe'), 'curl.exe pinjam+kembalikan');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
