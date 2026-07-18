<?php

/**
 * Content / checklist audit #43.
 * Usage: php scripts/audit-article43-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article43Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article43Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article43Seeder.php');

echo "=== Content / checklist audit #43 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[4-9]|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #44+ di luar link/pre');
check(str_contains($body, '#43 (ini)'), 'Self-ref #43 (ini)');
check(substr_count($body, '/artikel/attribute-method-constructor-init-python') >= 2, 'Minimal 2 tautan ke #42');
check(str_contains($body, '/artikel/class-dan-object-pertama-python'), 'Tautan ke #41');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Tautan ke #40');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'buku_terlindungi.py'), 'File contoh buku_terlindungi.py');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop43Arrow'), 'Marker id unik oop43');
check(str_contains($body, '@property'), 'Bahas @property');
check(str_contains($body, '@tahun.setter') || str_contains($body, '@stok.setter'), 'Bahas setter');
check(str_contains($body, 'RecursionError'), 'Bahas RecursionError');
check(str_contains($body, 'BukuSalah') && str_contains($body, 'BukuBenar'), 'Demo RecursionError SALAH/BENAR');
check(str_contains($body, '9/10 artikel live'), 'Progress 9/10 live');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Hardlink #43→#44');
check(str_contains($body, 'Inheritance'), 'Teaser/link Inheritance');
check(str_contains($body, 'juga lewat property') || str_contains($body, 'lewat property'), 'Jelaskan stok -= 1 lewat property');
check(strpos($body, '@setter') !== false && strpos($body, 'oop43Arrow') !== false && strpos($body, '@setter') < strpos($body, 'oop43Arrow'), 'SVG setelah intro setter');
check(substr_count($body, 'language-python') >= 5, 'Minimal 5 blok language-python');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article42Seeder.php'), 'encapsulation-property-python-oop'), 'Backlink #42→#43');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'encapsulation-property-python-oop'), 'Indeks #40→#43');
check(file_exists(__DIR__.'/audit-article43.php'), 'audit-article43.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-43'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-43'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle43'), 'DeployController');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
