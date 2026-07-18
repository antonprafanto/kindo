<?php

/**
 * Content / checklist audit #47.
 * Usage: php scripts/audit-article47-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article47Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article47Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article47Seeder.php');

echo "=== Content / checklist audit #47 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:49|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #49+ di luar link/pre');
check(str_contains($body, '#47 (ini)'), 'Self-ref #47 (ini)');
check(substr_count($body, '/artikel/abstraction-abc-python-oop') >= 2, 'Minimal 2 tautan ke #46');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Tautan ke #44');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Tautan ke #40');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_komposisi.py'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop47Arrow'), 'Marker id unik oop47');
check(str_contains($body, 'PerpustakaanSalah'), 'Anti-pola inheritance');
check(str_contains($body, 'self.koleksi'), 'Composition koleksi');
check(str_contains($body, 'PerpustakaanBenar') || str_contains($body, 'SALAH'), 'Refactor SALAH/BENAR');
check(str_contains($body, '/artikel/special-methods-dataclass-python'), 'Backlink live ke #48');
check(str_contains($body, 'dataclass') || str_contains($body, 'Special Methods'), 'Teaser/link #48');
check(! str_contains($body, 'dataclass (artikel berikutnya)'), 'Tanpa residual “dataclass (artikel berikutnya)”');
check(str_contains($body, '9/10 artikel live'), 'Progress 9/10 live');
check(str_contains($body, 'sering berpasangan'), 'Jembatan composition + ABC');
check(substr_count($body, '/artikel/abstraction-abc-python-oop') >= 3, 'Minimal 3 tautan ke #46 (termasuk jembatan)');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article46Seeder.php'), 'composition-vs-inheritance-python'), 'Backlink #46→#47');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'composition-vs-inheritance-python'), 'Indeks #40→#47');
check(substr_count($body, 'language-python') >= 7, 'Minimal 7 blok language-python');
check(str_contains($body, 'KatalogSalah') && str_contains($body, 'KatalogBenar'), 'Demo Katalog list vs composition');
$plainCheck = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/ABC\s*\(#46\)/', $plainCheck), 'Tidak ada bare ABC (#46)');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article47.php'), 'audit-article47.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-47'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-47'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle47'), 'DeployController');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php'), 'composition'), 'Tag composition di TagSeeder');
$a44 = file_get_contents(__DIR__.'/../database/seeders/Article44Seeder.php');
check(str_contains($a44, 'composition-vs-inheritance-python') && ! str_contains($a44, 'composition (nanti)'), 'Backlink #44 tanpa residual “nanti”');
check(! str_contains($body, 'Extrak') && ! str_contains($body, 'SensorDHT'), 'Tanpa typo Extrak / contoh SensorDHT asing');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
