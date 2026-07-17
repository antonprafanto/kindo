<?php

/**
 * Paranoid content audit #41 — checklist + outline roadmap.
 * Usage: php scripts/audit-article41-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article41Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article41Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article41Seeder.php');

echo "=== Content / checklist audit #41 ===\n\n";

// Checklist §1 hyperlink
$plainBody = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
$plainBody = preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body);
$plainNoLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(! preg_match('/(?<![\w\/"#>])#(?:4[2-9]|[5-9]\d)\b/', $plainNoLinks), 'Tidak ada plain #42+ di luar link/pre');
check(substr_count($body, 'mengenal-oop-cara-berpikir-dengan-objek-python') >= 2, 'Minimal 2 tautan ke #40 (pendahuluan + progress)');
check(str_contains($body, '(#40)'), 'Ada anchor (#40)');

check(str_contains($body, '#41 (ini)'), 'Self-ref #41 (ini)');

// Checklist §2 no ASCII
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');

// Checklist §3 dark bg
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0 (SVG + Pola Dasar)');

// Checklist §4 Pola Dasar
check(str_contains($body, 'Pola Dasar'), 'Ada heading Pola Dasar');
check(preg_match_all('/background:#2979FF/', $body) >= 2, 'Pola Dasar punya badge bernomor (biru)');

// Outline #41 dari roadmap
check(str_contains($body, 'class Buku'), 'Outline: syntax class Buku');
check(str_contains($body, 'buku_a') && str_contains($body, 'buku_b'), 'Outline: buat instance buku_a/buku_b');
check(str_contains($body, 'id('), 'Outline: identitas object id()');
check(str_contains($body, '<svg'), 'Outline: diagram class vs object');

// Kualitas konten Seri 3
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap salin-jalankan');
check(str_contains($body, 'buku_a == buku_b'), 'Klarifikasi == default di prose');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan singkat');
check(str_contains($body, 'FAQ singkat') || str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum / troubleshooting');
check(str_contains($body, 'language-python'), 'Highlight language-python');
check(substr_count($body, '<pre') >= 4, 'Minimal 4 blok kode');
check(str_contains($body, 'aria-label'), 'SVG figure punya aria-label');
check(str_contains($body, 'figcaption'), 'Ada figcaption');

// Jangan hardlink #42 sebelum live
check(! str_contains($body, '/artikel/attribute-method-constructor-init-python'), 'Tidak hardlink slug #42 (belum live)');
check(! preg_match('/\(#42\)/', $body), 'Tidak ada (#42) di body');

// Teaser next tanpa nomor broken
check(str_contains($body, 'Attribute, Method') || str_contains($body, '__init__'), 'Teaser artikel berikutnya tanpa slug mati');

// Seeder meta
check(str_contains($src, "'is_featured'     => false") || str_contains($src, "'is_featured' => false"), 'is_featured false');
check(str_contains($src, 'cover_image tidak disentuh'), 'Cover tidak di-overwrite');
check(str_contains($src, 'programming'), 'Kategori programming di seeder');

// Wiring files exist
check(is_file(__DIR__.'/audit-article41.php'), 'audit-article41.php ada');
check(is_file(__DIR__.'/../database/seeders/Article41Seeder.php'), 'Article41Seeder.php ada');

$routes = file_get_contents(__DIR__.'/../routes/web.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
check(str_contains($routes, 'publish-article-41'), 'Route hook');
check(str_contains($yml, 'publish-article-41'), 'CI step');
check(str_contains($deploy, 'publishArticle41'), 'DeployController method');

// Backlink #40 → #41 (wajib sebelum bundel deploy)
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
check(str_contains($a40, 'class-dan-object-pertama-python'), 'Backlink #40→#41 di Article40Seeder');
check(str_contains($a40, '(#41)'), 'Anchor (#41) di Article40Seeder');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
