<?php

/**
 * Content / checklist audit #60.
 * Usage: php scripts/audit-article60-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article60Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #60 ===\n\n";

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article60Seeder.php');
$slug = 'laravel-controller-service-eloquent';

check(str_contains($body, '#60 (ini)'), 'Self-ref #60 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:6[1-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #61+');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Link #59');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_controller_service_eloquent_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel60cseArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 3, '≥3 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-60'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '5/8'), 'Progress 5/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor');
check(! preg_match('/<a\b[^>]*>\s*#\d+\s*<\/a>/u', $body), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article60.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article60-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article60-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article60-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'loket') && str_contains($body, 'dapur'), 'Narasi loket/dapur');
check(str_contains($body, '/api/buku'), 'Path /api/buku');
check(str_contains($body, 'BukuController'), 'BukuController');
check(! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'PHP 8.3+'), 'Versi PHP awam');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'install-dari-nol'), 'Aturan install-dari-nol');
check(str_contains($body, 'demo()'), 'Demo fungsi');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'Browser'), 'Daftar alat awam');
check(str_contains($body, 'Explorer'), 'Explorer di daftar alat');
check(str_contains($body, 'mkdir app\\Services') || str_contains($body, 'mkdir app/Services'), 'mkdir Services');
check(str_contains($body, 'create_bukus_table'), 'Petunjuk file migrasi');
check(str_contains($body, 'dir database\\migrations') || str_contains($body, 'dir database/migrations'), 'Perintah dir migrations');
check(str_contains($body, 'notepad app\\Models\\Buku.php') || str_contains($body, 'notepad app/Models/Buku.php'), 'notepad model Buku');
check(str_contains($body, 'cd C:\\laragon\\www\\perpustakaan-api') || str_contains($body, 'perpustakaan-api'), 'cd folder proyek');
check(str_contains($body, 'terminal kedua'), 'Tip terminal kedua');
check(str_contains($body, 'Terminal mana') || str_contains($body, 'Shell XAMPP'), 'FAQ terminal');
check(str_contains($body, 'Start Menu') || str_contains($body, 'CMD/PowerShell'), 'Peringatan terminal salah');
check(! str_contains($body, 'Tinker'), 'Tanpa jargon Tinker');
$withoutLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '';
$bare59 = preg_match_all('/#59(?!\s*\(ini\))/', $withoutLinks);
$bare57 = preg_match_all('/#57(?!\s*\(ini\))/', $withoutLinks);
check($bare59 === 0 && $bare57 === 0, 'Tanpa bare #57/#59 ('.$bare57.'/'.$bare59.')');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
