<?php

/**
 * Content / checklist audit #46.
 * Usage: php scripts/audit-article46-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article46Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article46Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article46Seeder.php');

echo "=== Content / checklist audit #46 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[8-9]|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #48+ di luar link/pre');
check(str_contains($body, '#46 (ini)'), 'Self-ref #46 (ini)');
check(substr_count($body, '/artikel/polymorphism-python-oop') >= 2, 'Minimal 2 tautan ke #45');
check(str_contains($body, '/artikel/encapsulation-property-python-oop'), 'Tautan ke #43');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Tautan ke #40');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'kontrak_pinjaman.py'), 'File contoh kontrak_pinjaman.py');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop46Arrow'), 'Marker id unik oop46');
check(str_contains($body, 'class Pinjaman'), 'Kontrak Pinjaman');
check(str_contains($body, 'abstractmethod'), 'abstractmethod');
check(str_contains($body, 'BukuFisik') && str_contains($body, 'EbookLisensi'), 'Dua implementasi');
check(str_contains($body, 'BukuBelumSiap'), 'Demo subclass belum siap');
check(str_contains($body, 'TypeError'), 'TypeError abstract');
check(str_contains($body, '/artikel/composition-vs-inheritance-python'), 'Hardlink #47 Composition');
check(str_contains($body, 'Composition'), 'Teaser Composition tanpa slug mati');
check(str_contains($body, '10/10 artikel live'), 'Progress 10/10 live');
check(str_contains($body, 'isinstance(item, Pinjaman)'), 'isinstance kontrak');
check(str_contains($body, 'hutan'), 'Bedakan hutan isinstance #45');
check(str_contains($body, 'except TypeError'), 'TypeError runnable try/except');
check(str_contains($body, 'bukan silsilah katalog'), 'Klarifikasi bukan warisi Buku');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article45Seeder.php'), 'abstraction-abc-python-oop'), 'Backlink #45→#46');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'abstraction-abc-python-oop'), 'Indeks #40→#46');
check(substr_count($body, 'language-python') >= 8, 'Minimal 8 blok language-python');
check(str_contains($body, 'EntriDuck'), 'Demo EntriDuck vs ABC');
check(str_contains($body, 'BukuFisik -> pinjam Cerita Sensor'), 'Output demo label()');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article46.php'), 'audit-article46.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-46'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-46'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle46'), 'DeployController');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
