<?php

/**
 * Content / checklist audit #57.
 * Usage: php scripts/audit-article57-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article57Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #57 ===\n\n";

$ref = new ReflectionClass(Article57Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

check(str_contains($body, '#57 (ini)'), 'Self-ref #57 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[89]|6[0-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak plain #58+');
check(str_contains($body, '/artikel/laravel-instalasi-proyek-pertama'), 'Link #56');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_struktur_env_artisan_demo.php'), 'File contoh');
check(str_contains($body, '<h2>Latihan</h2>'), 'Latihan');
check(str_contains($body, '<h2>FAQ</h2>'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel57structArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 3, '≥3 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'laravel-struktur-env-artisan'), 'Slug');
check(str_contains($routes, 'publish-article-57'), 'Route hook');
check(str_contains($yml, 'laravel-struktur-env-artisan'), 'CI slug');
check(str_contains($body, '2/8'), 'Progress 2/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy|thin anchor/i', $body), 'Tanpa suara editor');
check(! preg_match('/(?<![\w\/"#>(ini)\s])#5[67](?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article57.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article57-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article57-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article57-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'denah'), 'Narasi denah');
check(str_contains($body, 'database.sqlite'), 'File sqlite disebut');
check(str_contains($body, 'type nul') || str_contains($body, 'Text File'), 'Cara buat sqlite Windows');
check(str_contains($body, 'database.sqlite.txt') || str_contains($body, '.sqlite.txt'), 'Peringatan ekstensi .txt Windows');
check(str_contains($body, 'Bagaimana membuat file database.sqlite'), 'FAQ buat sqlite');
check(str_contains($body, 'key:generate'), 'key:generate');
check(! str_contains($body, 'Pin ') && ! str_contains($body, 'closure'), 'Tanpa Pin/closure');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'PHP 8.3+'), 'Versi PHP awam');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Hardlink #58');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'install-dari-nol'), 'Aturan install-dari-nol');
check(str_contains($body, 'petaFolder') || str_contains($body, 'demo()'), 'Demo fungsi');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'Explorer'), 'Daftar alat awam');
check(str_contains($body, 'cd C:\\laragon\\www\\perpustakaan-api') || str_contains($body, 'perpustakaan-api'), 'cd folder proyek');
check(str_contains($body, 'editor teks') && str_contains($body, 'DB_CONNECTION'), 'Petunjuk editor .env');
check(str_contains($body, 'Terminal mana') || str_contains($body, 'Shell XAMPP'), 'FAQ terminal');
check(str_contains($body, 'notepad .env'), 'Buka .env via notepad (Windows)');
check(str_contains($body, 'ganti') && str_contains($body, 'DB_CONNECTION=sqlite'), 'Ganti DB_CONNECTION ke sqlite');
check(str_contains($body, 'terminal kedua'), 'Tip terminal kedua saat serve');
check(str_contains($body, 'Start Menu') || str_contains($body, 'CMD/PowerShell'), 'Peringatan terminal salah');
$withoutLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '';
$bare56 = preg_match_all('/#56(?!\s*\(ini\))/', $withoutLinks);
$thinLink56 = preg_match_all('/<a\b[^>]*>\s*#56\s*<\/a>/', $body);
check($bare56 === 0 && $thinLink56 === 0, 'Tanpa bare/thin #56 ('.$bare56.'/'.$thinLink56.')');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
