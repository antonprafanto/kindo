<?php

/**
 * Content / checklist audit #56.
 * Usage: php scripts/audit-article56-content.php
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

echo "=== Content / checklist audit #56 ===\n\n";

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php');
$slug = 'laravel-instalasi-proyek-pertama';
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(str_contains($body, '#56 (ini)'), 'Self-ref #56 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[7-9]|6[0-3])(?!\s*\(ini\))/', $plain), 'Tidak plain #57+');
check(str_contains($body, '/artikel/oop-php-visibility-composition'), 'Link #55');
check(str_contains($body, '/artikel/oop-php-property-method-constructor'), 'Soft mention #54');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_instalasi_proyek_pertama_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel56installArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-56'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '1/8'), 'Progress 1/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'Belum perlu hardlink') && ! str_contains($body, 'soft, belum hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php'), $slug), '#55 hardlink #56');
check(file_exists(__DIR__.'/audit-article56.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article56-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article56-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article56-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'Kenapa instal'), 'Narasi instal dulu');
check(str_contains($body, 'mendirikan toko') || str_contains($body, 'perpustakaan'), 'Analogi toko/perpustakaan');
check(str_contains($body, 'create-project') && str_contains($body, 'perpustakaan-api'), 'Gloss create-project');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'PHP 8.3+'), 'Versi PHP awam');
check(str_contains($body, 'Laravel Framework 13'), 'Demo Artisan Laravel 13');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa Laravel 11+ usang');
check(! str_contains($body, '11.31.0'), 'Tanpa nomor 11.31 usang');
check(str_contains($body, 'laragon.org'), 'Link Laragon klikable');
check(str_contains($body, 'windows.php.net'), 'Link PHP Windows klikable');
check(str_contains($body, 'getcomposer.org'), 'Link Composer klikable');
check(str_contains($body, 'struktur folder') && str_contains($body, '.env') && ! str_contains($body, '/artikel/laravel-struktur-env-artisan'), 'Soft bridge #57 tanpa hardlink');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'proyek') && ! preg_match('/(?<!-)project /', $body), 'Proyek Laravel');
check(! str_contains($body, 'endpoint'), 'Tanpa endpoint');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'install-dari-nol'), 'Aturan install-dari-nol');
check(str_contains($body, 'php -v') && str_contains($body, 'composer -V'), 'Cek versi');
check(! str_contains($body, 'authorization policy'), 'Tanpa authorization mentah');
check(! str_contains($body, 'supaya UI '), 'Tanpa jargon UI');
check(substr_count($body, '<a ') - substr_count($body, '</a>') === 0, 'Thin anchor balance');
$noSvg = preg_replace('/<svg\b.*?<\/svg>/is', '', $body) ?? $body;
$noA = preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $noSvg) ?? $noSvg;
check(! preg_match('/(?<![\w\/"#>])#5[3-6](?!\s*\(ini\))/', strip_tags($noA)), 'Thin/bare #53-56 = 0');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
