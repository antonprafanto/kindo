<?php

/**
 * Paranoid content audit #42 — checklist + konsistensi pedagogi.
 * Usage: php scripts/audit-article42-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article42Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article42Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article42Seeder.php');

echo "=== Content / checklist audit #42 ===\n\n";

$plainNoLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(! preg_match('/(?<![\w\/"#>])#(?:4[3-9]|[5-9]\d)\b/', $plainNoLinks), 'Tidak ada plain #43+ di luar link/pre');
check(str_contains($body, '#42 (ini)'), 'Self-ref #42 (ini)');
check(substr_count($body, 'class-dan-object-pertama-python') >= 2, 'Minimal 2 tautan ke #41');
check(substr_count($body, 'mengenal-oop-cara-berpikir-dengan-objek-python') >= 1, 'Tautan ke #40');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_mini.py'), 'File contoh perpustakaan_mini.py');
check(str_contains($body, 'Latihan singkat'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'oop42Arrow'), 'Marker id unik oop42');
check(! str_contains($body, 'dipinjam'), 'State konsisten: tidak pakai dipinjam yatim');
check(str_contains($body, 'class Buku:') && str_contains($body, 'def pinjam_untuk(self'), 'pinjam_untuk lengkap dalam class');
check(str_contains($body, 'self.riwayat = []') || str_contains($body, 'Jebakan klasik'), 'Peringatan list bersama di class');
check(str_contains($body, 'BukuSalah') && str_contains($body, 'BukuBenar'), 'Demo SALAH/BENAR list class bersama');
check(str_contains($body, '6/10 artikel live'), 'Progress 6/10 live');
check(str_contains($body, '/artikel/encapsulation-property-python-oop'), 'Forward link #42→#43');
check(str_contains($body, '(#43)'), 'Anchor (#43) di forward link');
check(substr_count($body, '<pre') >= 6, 'Minimal 6 blok kode (termasuk demo list bersama)');
check(str_contains($src, "'is_featured'     => false") || str_contains($src, "'is_featured' => false"), 'is_featured false');
check(str_contains($src, 'cover_image tidak disentuh'), 'Cover tidak di-overwrite');

$a41 = file_get_contents(__DIR__.'/../database/seeders/Article41Seeder.php');
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
check(str_contains($a41, 'attribute-method-constructor-init-python'), 'Backlink #41→#42');
check(str_contains($a40, 'attribute-method-constructor-init-python'), 'Indeks #40→#42');

check(is_file(__DIR__.'/audit-article42.php'), 'audit-article42.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-42'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-42'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle42'), 'DeployController');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
