<?php

/**
 * Audit utama #63 — CRUD API Buku: Ubah & Hapus (Seri 4).
 * Usage: php scripts/audit-article63.php
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

echo "=== Audit Artikel #63 — CRUD API Buku Ubah & Hapus ===\n\n";

$ref = new ReflectionClass(Article63Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article63Seeder.php');

check(str_contains($body, '#63 (ini)'), 'Self-ref');
check(str_contains($body, 'BukuController') && str_contains($body, 'auth:sanctum'), 'BukuController + auth:sanctum');
check(str_contains($body, 'Bearer') || str_contains($body, 'token'), 'Token / Bearer');
check(str_contains($body, 'laravel63crudArrow'), 'SVG marker');
check(str_contains($body, 'viewBox="0 0 760 240"'), 'SVG horizontal');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'laravel_crud_api_buku_ubah_hapus_demo.php'), 'File contoh');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(str_contains($body, 'language-php'), 'language-php');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($src, $slug), 'Slug di seeder');
check(str_contains($body, '/artikel/capstone-api-perpustakaan-laravel'), 'Link #62');
check(str_contains($body, '/artikel/laravel-auth-api-dasar'), 'Link #61');
check(str_contains($body, '/artikel/laravel-controller-service-eloquent'), 'Link #60');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Link #59');
check(str_contains($body, '/artikel/laravel-struktur-env-artisan'), 'Link #57');
check(str_contains($body, '/artikel/laravel-instalasi-proyek-pertama'), 'Link #56');
check(str_contains($body, 'update') && str_contains($body, 'destroy'), 'Method update + destroy');
check(str_contains($body, 'PUT /api/buku') && str_contains($body, 'DELETE /api/buku'), 'PUT + DELETE path');
check(str_contains($body, '{id}'), 'Route parameter {id}');
check(str_contains($body, '404') && str_contains($body, 'Buku tidak ditemukan'), 'Narasi 404 awam');
check(str_contains($body, 'CRUD'), 'Narasi CRUD');
check(str_contains($body, 'install-dari-nol'), 'Marker install-dari-nol');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'terminal kedua'), 'Petunjuk tools awam');
check(str_contains($body, 'Cara buka terminal kedua'), 'Cara buka terminal kedua eksplisit');
check(str_contains($body, 'cara salin teks dari terminal'), 'Cara salin teks terminal');
check(str_contains($body, 'curl.exe'), 'curl.exe uji');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

check(str_contains($routes, 'publish-article-63'), 'Route hook');
check(str_contains($yml, $slug), 'CI slug');
check(str_contains($yml, 'Publish article 63 via deploy hook (kickoff)'), 'CI #63 step');
check(preg_match('/Publish article 63 via deploy hook \(kickoff\)\s*\n\s*continue-on-error:\s*true/u', $yml) === 1, 'CI #63 kickoff (continue-on-error)');
check(str_contains($deploy, 'publishArticle63'), 'DeployController');
check(str_contains($deploy, $slug), 'Hook cek slug');
check(str_contains($deploy, 'laravel63crudArrow'), 'Hook cek marker SVG');
check(! str_contains(file_get_contents(__DIR__.'/../database/seeders/Article62Seeder.php'), $slug), 'Tanpa hardlink #62→#63 (menyusul setelah LIVE)');
check(file_exists(__DIR__.'/audit-article63-php.php'), 'audit-article63-php.php');
check(file_exists(__DIR__.'/audit-article63-sanitize.php'), 'audit-article63-sanitize.php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');
check(str_contains($src, 'prevPublished'), 'Urutan publish setelah #62');
check(str_contains($body, '8/8'), 'Progress 8/8');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel 13+');
check(str_contains($body, 'PHP 8.3+'), 'Syarat PHP 8.3+');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin Laravel 11+ usang');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, '↔'), 'Tanpa Unicode lr-arrow');
check(! str_contains($body, 'closure'), 'Tanpa jargon closure');
check(! str_contains($body, 'endpoint'), 'Tanpa jargon endpoint');
check(str_contains($body, 'lanjutan'), 'Soft bridge lanjutan');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(! str_contains($src, 'laravel-pagination-filter-pencarian'), 'Tanpa slug pagination Seri 5 usang');
check(! str_contains($src, 'laravel-eloquent-relasi-peminjaman'), 'Tanpa slug relasi Seri 5 usang');
check(! str_contains($body, 'Seri 5'), 'Tanpa framing Seri 5 usang');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
