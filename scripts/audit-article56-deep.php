<?php

/**
 * Deep-audit pass-1 #56 — instal PHP/Composer/Laravel ramah awam.
 * Usage: php scripts/audit-article56-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article56Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #56 ===\n\n";

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php');
$plain = trim(preg_replace('/\s+/u', ' ', strip_tags($body)) ?? '');
$words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']*)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.(isset($m[1]) ? mb_strlen($m[1]) : 0).')');
check(str_contains($body, 'create-project') && str_contains($body, 'composer create-project'), 'create-project');
check(str_contains($body, 'artisan serve') && str_contains($body, 'php artisan --version'), 'artisan serve + version');
check(str_contains($body, 'Kenapa instal') || str_contains($body, 'install-dari-nol'), 'Fondasi instal dulu');
check(str_contains($body, 'Seri 4') && str_contains($body, '#56 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel');
check(str_contains($body, 'PHP 8.3'), 'Syarat PHP 8.3');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin 11+ usang');
check(! str_contains($body, 'PHP 8.2+'), 'Tanpa PHP 8.2+ usang');
check(str_contains($body, 'laragon.org'), 'Link Laragon');
check(str_contains($body, 'windows.php.net'), 'Link PHP Windows');
check(str_contains($body, 'getcomposer.org'), 'Link Composer');
check(substr_count($body, '/artikel/oop-php-visibility-composition') >= 2, '≥2 link #55');
check(str_contains($body, '/artikel/oop-php-property-method-constructor'), 'Soft mention #54');
check(! preg_match('/(?<![\w\/"#>])#56(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #56 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[7-9]|6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #57+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#55(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #55');
check(! preg_match('/(?<![\w\/"#>])#54(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #54');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'laravel_instalasi_proyek_pertama_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-instalasi-proyek-pertama'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle56'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 56 via deploy hook (required)'), 'CI #56 required');
check(! preg_match('/Publish article 56 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #56 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php'), 'laravel-instalasi-proyek-pertama'), '#55 hardlink #56');
check(str_contains($body, '1/8'), 'Progress 1/8');
check(str_contains($body, 'Web Lanjut v2') || str_contains($body, 'jalur Laravel'), 'Framing Seri 4 v2');
check(str_contains($body, 'Arti awam') || str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, '/artikel/laravel-struktur-env-artisan'), 'Hardlink #57');
check(str_contains($body, 'mendirikan toko') || str_contains($body, 'perpustakaan'), 'Analogi toko');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'strict_types') && str_contains($body, 'tipe'), 'Gloss strict_types');
check(str_contains($body, 'proyek') && ! preg_match('/(?<!-)project /', $body), 'Proyek (bukan project)');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, 'php -v') && str_contains($body, 'composer -V'), 'Cek versi');
check(str_contains($body, 'Laragon') || str_contains($body, 'XAMPP'), 'Jalur Windows');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'terminal Laragon'), 'Petunjuk tools awam');
check(str_contains($body, 'Shell') && (str_contains($body, 'Variabel lingkungan') || str_contains($body, 'Environment Variables')), 'Residual XAMPP + PATH');
check(! str_contains($body, '/artikel/laravel-request-validasi-api'), 'Tanpa hardlink slug lama');
check(! str_contains($body, 'Belum perlu hardlink') && ! str_contains($body, 'soft, belum hardlink'), 'Tanpa suara editor hardlink');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');

echo "\n=== Deep-audit pass-1 #56: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: LIVE #56 — residual XAMPP/PATH siap oke deploy.\n";
}
exit($failed > 0 ? 1 : 0);
