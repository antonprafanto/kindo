<?php

/**
 * Content / checklist audit #63 — CRUD API Buku: Ubah & Hapus.
 * Usage: php scripts/audit-article63-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article63Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'laravel-crud-api-buku-ubah-hapus';

echo "=== Content / checklist audit #63 ===\n\n";

$ref = new ReflectionClass(Article63Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article63Seeder.php');
$withoutLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '';

check(str_contains($body, '#63 (ini)'), 'Self-ref #63 (ini)');
check(str_contains($body, '/artikel/capstone-api-perpustakaan-laravel'), 'Link #62');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tidak panah Unicode');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_crud_api_buku_ubah_hapus_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Latihan');
check(str_contains($body, 'FAQ'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel63crudArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 3, '≥3 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, $slug), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-63'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), $slug), 'CI slug');
check(str_contains($body, '8/8'), 'Progress 8/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor');
check(! preg_match('/(?<![\w\/"#>(ini)\s])#5[6-9](?!\s*\(ini\))/', $withoutLinks), 'Tanpa thin anchor #N');
check(file_exists(__DIR__.'/audit-article63.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article63-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article63-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article63-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'CRUD') && str_contains($body, 'hapus'), 'Narasi CRUD/hapus');
check(str_contains($body, 'PUT /api/buku') && str_contains($body, 'DELETE /api/buku'), 'Path PUT + DELETE');
check(str_contains($body, 'BukuController'), 'BukuController');
check(str_contains($body, 'update') && str_contains($body, 'destroy'), 'update + destroy');
check(str_contains($body, '{id}'), 'Route parameter {id}');
check(str_contains($body, '"id"'), 'Jelaskan asal nomor dari kunci id');
check(str_contains($body, '404'), 'Jelaskan 404');
check(! str_contains($body, 'Pin ') && ! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
check(str_contains($body, 'Laravel 13+'), 'Versi Laravel awam');
check(str_contains($body, 'PHP 8.3+'), 'Versi PHP awam');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa PHPDoc @param di demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(str_contains($body, 'install-dari-nol'), 'Aturan install-dari-nol');
check(str_contains($body, 'demo()'), 'Demo fungsi');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'Browser'), 'Daftar alat awam');
check(str_contains($body, 'Explorer'), 'Explorer di daftar alat');
check(str_contains($body, 'perpustakaan-api'), 'cd folder proyek');
check(str_contains($body, 'terminal kedua'), 'Tip terminal kedua');
check(str_contains($body, 'Cara buka terminal kedua'), 'Cara buka terminal kedua eksplisit');
check(str_contains($body, 'cara salin teks dari terminal'), 'Cara salin teks terminal');
check(str_contains($body, 'notepad app\\Http\\Controllers\\BukuController.php'), 'Contoh path notepad konkret');
check(str_contains($body, 'Terminal mana') || str_contains($body, 'Shell XAMPP'), 'FAQ terminal');
check(str_contains($body, 'Start Menu') || str_contains($body, 'CMD/PowerShell'), 'Peringatan terminal salah');
check(str_contains($body, 'curl.exe'), 'curl.exe');
check(str_contains($body, 'Opsi C') || str_contains($body, 'Postman'), 'Opsi alat berjendela');
check(str_contains($body, 'salin token') || str_contains($body, '"token"'), 'Tip salin token');
check(! str_contains($body, '%%{http_code}'), 'Tanpa %%{http_code} membingungkan');
check(str_contains($body, 'Accept: application/json'), 'Header Accept');
check(str_contains($body, 'bilah alamat browser'), 'Peringatan PUT/DELETE bukan lewat browser');
$bare62 = preg_match_all('/#62(?!\s*\(ini\))/', $withoutLinks);
check($bare62 === 0, 'Tanpa bare #62 ('.$bare62.')');
$bare64 = preg_match_all('/#6[4-9]/', $withoutLinks);
check($bare64 === 0, 'Tanpa bare #64+ ('.$bare64.')');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
