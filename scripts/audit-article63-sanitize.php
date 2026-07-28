<?php

/**
 * Sanitize spot-check #63 — runs the REAL ArticleHtmlSanitizer (same as
 * the Article model mutator) and verifies required markers survive.
 * Usage: php scripts/audit-article63-sanitize.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article63Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Sanitize spot-check #63 (real sanitizer) ===\n\n";

$ref = new ReflectionClass(Article63Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$raw = $method->invoke($ref->newInstanceWithoutConstructor());

$body = app(ArticleHtmlSanitizer::class)->sanitize($raw);

check(str_contains($body, 'laravel63crudArrow'), 'SVG marker survives sanitize');
check(str_contains($body, 'viewBox="0 0 760 240"'), 'viewBox survives sanitize');
check(str_contains($body, '<figcaption'), 'figcaption');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar style survives sanitize');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar H2');
check(substr_count($body, '<h2') >= 8, '≥8 H2');
check(str_contains($body, 'language-php'), 'language-php');
check(str_contains($body, 'laravel_crud_api_buku_ubah_hapus_demo.php'), 'File contoh');
check(str_contains($body, 'BukuController') && str_contains($body, 'destroy'), 'CRUD/Buku markers');
check(str_contains($body, 'Buku tidak ditemukan'), 'Marker 404 awam survives');
check(str_contains($body, 'Cara buka terminal kedua'), 'Petunjuk terminal kedua survives');
check(substr_count($body, '#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(strlen($body) > 2000, 'Body tidak terpotong pendek');
check(strlen($raw) - strlen($body) < 500, 'Sanitizer tidak membuang banyak konten (raw='.strlen($raw).' clean='.strlen($body).')');

$methodEn = $ref->getMethod('bodyEn');
$methodEn->setAccessible(true);
$rawEn = $methodEn->invoke($ref->newInstanceWithoutConstructor());
$bodyEn = app(ArticleHtmlSanitizer::class)->sanitize($rawEn);
check(str_contains($bodyEn, 'laravel63crudArrow'), 'EN SVG marker survives sanitize');
check(str_contains($bodyEn, '#63 (this article)'), 'EN self-ref survives sanitize');
check(str_contains($bodyEn, 'Beginner:'), 'EN Beginner marker survives sanitize');
check(str_contains($bodyEn, 'curl.exe'), 'EN curl.exe survives sanitize');
check(strlen($rawEn) - strlen($bodyEn) < 800, 'EN sanitizer tidak membuang banyak konten');

echo "\n=== Sanitize spot-check #63: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
