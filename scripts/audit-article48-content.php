<?php

/**
 * Content / checklist audit #48.
 * Usage: php scripts/audit-article48-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article48Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article48Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article48Seeder.php');

echo "=== Content / checklist audit #48 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:49|[5-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #49+ di luar link/pre');
check(str_contains($body, '#48 (ini)'), 'Self-ref #48 (ini)');
check(substr_count($body, '/artikel/composition-vs-inheritance-python') >= 2, 'Minimal 2 tautan ke #47');
check(str_contains($body, '/artikel/attribute-method-constructor-init-python'), 'Tautan ke #42');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Tautan ke #40');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'buku_special_methods.py'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop48Arrow'), 'Marker id unik oop48');
check(str_contains($body, '__str__') && str_contains($body, '__repr__') && str_contains($body, '__eq__'), 'Cover __str__/__repr__/__eq__');
check(str_contains($body, '@dataclass') || str_contains($body, 'dataclass'), 'Cover dataclass');
check(str_contains($body, 'NotImplemented'), 'Demo NotImplemented di __eq__');
check(! str_contains($body, '/artikel/capstone-sistem-perpustakaan-mini-oop-python'), 'Tidak hardlink #49');
check(str_contains($body, 'Capstone') || str_contains($body, 'Perpustakaan Mini'), 'Teaser #49 tanpa slug mati');
check(str_contains($body, '9/10 artikel live'), 'Progress 9/10 live');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article47Seeder.php'), 'special-methods-dataclass-python') && ! str_contains(file_get_contents(__DIR__.'/../database/seeders/Article47Seeder.php'), 'dataclass (artikel berikutnya)'), 'Backlink #47 tanpa residual “artikel berikutnya”');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'special-methods-dataclass-python'), 'Indeks #40→#48');
check(substr_count($body, 'language-python') >= 8, 'Minimal 8 blok language-python');
check(str_contains($body, 'class Katalog') && str_contains($body, 'items'), 'Full code composition + dataclass');
check(str_contains($body, 'unhashable') || str_contains($body, '__hash__'), 'Bahas unhashable / __hash__');
check(str_contains($body, 'tanpa __eq__, a == b?'), 'Demo default == False sebelum __eq__');
$plainCheck = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#(?:4[0-7]|49)(?!\s*\(ini\))/', $plainCheck), 'Tidak ada bare #40–#47/#49 di prosa');
$reprPos = strpos($body, '__repr__</code> — untuk developer');
$svgPos = strpos($body, 'oop48Arrow');
check($reprPos !== false && $svgPos !== false && $svgPos > $reprPos, 'SVG setelah section __repr__');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article48.php'), 'audit-article48.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-48'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-48'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle48'), 'DeployController');
check(str_contains($body, 'dunder'), 'Sebut sinonim dunder method');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Latihan taut ke #44');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php'), 'dataclass'), 'Tag dataclass di TagSeeder');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
