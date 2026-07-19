<?php

/**
 * Content / checklist audit #44.
 * Usage: php scripts/audit-article44-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article44Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article44Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article44Seeder.php');

echo "=== Content / checklist audit #44 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:4[5-9]|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #45+ di luar link/pre');
check(str_contains($body, '#44 (ini)'), 'Self-ref #44 (ini)');
check(substr_count($body, '/artikel/encapsulation-property-python-oop') >= 2, 'Minimal 2 tautan ke #43');
check(str_contains($body, '/artikel/attribute-method-constructor-init-python'), 'Tautan ke #42');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Tautan ke #40');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_ebook.py'), 'File contoh perpustakaan_ebook.py');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop44Arrow'), 'Marker id unik oop44');
check(str_contains($body, 'class Ebook(Buku)'), 'Bahas Ebook(Buku)');
check(str_contains($body, 'super().__init__'), 'Bahas super().__init__');
check(str_contains($body, 'super().info()'), 'Override memakai super().info()');
check(str_contains($body, '__nama') || str_contains($body, 'name-mangling'), 'FAQ __ / name-mangling (teaser #43)');
check(str_contains($body, 'menggantikan'), '__init__ anak menggantikan induk');
check(! str_contains(strtolower(strip_tags($body)), 'majalah'), 'Tidak ada majalah yatim di prosa');
check(str_contains($body, 'Audiobook'), 'Audiobook di SVG/latihan');
check(str_contains($body, 'tanpa') && str_contains($body, '@property'), 'Klarifikasi tanpa @property');
check(str_contains($body, 'tetap object'), 'Jelaskan self tetap object anak');
check(str_contains($body, 'EbookSalah'), 'Demo EbookSalah');
check(str_contains($body, 'EbookBenar'), 'Demo EbookBenar');
check(str_contains($body, 'AttributeError'), 'Bahas AttributeError');
check(str_contains($body, 'Output yang diharapkan'), 'Output contoh setelah kode lengkap');
check(str_contains($body, 'type(e) is Buku'), 'Bedakan type is vs isinstance');
$overridePos = strpos($body, 'Override method info()');
$svgPos = strpos($body, 'oop44Arrow');
check($overridePos !== false && $svgPos !== false && $svgPos > $overridePos, 'SVG setelah override');
check(str_contains($body, '/artikel/polymorphism-python-oop'), 'Hardlink #44→#45');
check(str_contains($body, 'Polymorphism'), 'Teaser/link Polymorphism');
check(str_contains($body, '10/10 artikel live'), 'Progress 10/10 live');
check(substr_count($body, 'language-python') >= 5, 'Minimal 5 blok language-python');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article43Seeder.php'), 'inheritance-pewarisan-class-python'), 'Backlink #43→#44');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'inheritance-pewarisan-class-python'), 'Indeks #40→#44');
check(file_exists(__DIR__.'/audit-article44.php'), 'audit-article44.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-44'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-44'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle44'), 'DeployController');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
