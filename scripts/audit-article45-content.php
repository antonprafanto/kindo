<?php

/**
 * Content / checklist audit #45.
 * Usage: php scripts/audit-article45-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article45Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article45Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article45Seeder.php');

echo "=== Content / checklist audit #45 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[6-9]|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #46+ di luar link/pre');
check(str_contains($body, '#45 (ini)'), 'Self-ref #45 (ini)');
check(substr_count($body, '/artikel/inheritance-pewarisan-class-python') >= 2, 'Minimal 2 tautan ke #44');
check(str_contains($body, '/artikel/encapsulation-property-python-oop'), 'Tautan ke #43');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Tautan ke #40');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'koleksi_polimorfik.py'), 'File contoh koleksi_polimorfik.py');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop45Arrow'), 'Marker id unik oop45');
check(str_contains($body, 'for item in koleksi'), 'Loop polimorfik');
check(str_contains($body, 'KatalogEntry'), 'Duck typing KatalogEntry');
check(str_contains($body, 'cetak_salah') && str_contains($body, 'cetak_benar'), 'Demo SALAH/BENAR isinstance');
check(str_contains($body, 'unduh'), 'isinstance untuk unduh');
check(str_contains($body, '/artikel/abstraction-abc-python-oop'), 'Hardlink #46 Abstraction');
check(str_contains($body, 'Abstraction') || str_contains($body, 'ABC'), 'Teaser Abstraction tanpa slug mati');
check(str_contains($body, '10/10 artikel live'), 'Progress 10/10 live');
check(str_contains($body, 'tipe object yang sebenarnya'), 'Method lookup tipe aktual');
check(str_contains($body, 'cek tipe anak'), 'Jebakan urutan isinstance');
check(str_contains($body, 'AttributeError') && str_contains($body, 'dict'), 'Demo AttributeError dict');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article44Seeder.php'), 'polymorphism-python-oop'), 'Backlink #44→#45');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'polymorphism-python-oop'), 'Indeks #40→#45');
check(str_contains($body, 'cabang Buku'), 'Demo jebakan urutan isinstance');
check(str_contains($body, 'anti-pola') || str_contains($body, 'cetak_salah(koleksi)'), 'Tandai cetak_salah anti-pola');
check(substr_count($body, 'language-python') >= 7, 'Minimal 7 blok language-python');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article45.php'), 'audit-article45.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-45'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-45'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle45'), 'DeployController');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
