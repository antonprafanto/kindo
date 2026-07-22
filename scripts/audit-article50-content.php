<?php

/**
 * Content / checklist audit #50.
 * Usage: php scripts/audit-article50-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article50Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article50Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');

echo "=== Content / checklist audit #50 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[2-9]|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #52+ di luar link/pre');
check(str_contains($body, '/artikel/oop-micropython-esp32-class-sensor'), 'Hardlink Tier 2 #51');
check(str_contains($body, '#50 (ini)'), 'Self-ref #50 (ini)');
check(substr_count($body, '/artikel/capstone-sistem-perpustakaan-mini-oop-python') >= 2, 'Minimal 2 tautan ke #49');
check(str_contains($body, '/artikel/polymorphism-python-oop'), 'Tautan ke #45');
check(str_contains($body, '/artikel/abstraction-abc-python-oop'), 'Tautan ke #46');
check(str_contains($body, '/artikel/composition-vs-inheritance-python'), 'Tautan ke #47');
check(str_contains($body, '/artikel/encapsulation-property-python-oop'), 'Tautan ke #43');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Tautan ke #40');
check(str_contains($body, 'lib.items') || str_contains($body, 'for item in lib.items'), 'Demo pakai lib.items');
check(! preg_match('/for item in lib\._items/', $body), 'Tidak loop _items di demo');
check(str_contains($body, 'error: jenis tidak dikenal') || str_contains($body, 'majalah'), 'Demo ValueError majalah');
check(str_contains($body, 'langkah <strong>#50 (ini)</strong>') || str_contains($body, 'langkah <strong>#50'), 'Footer langkah #50 (bukan draft)');
check(! str_contains($body, 'Factory di level Seri 3'), 'Tidak ada residual “Factory di level Seri 3”');
check(str_contains($src, 'composition') && str_contains($src, 'design-pattern'), 'Tag composition + design-pattern di seeder');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'factory_strategy_perpustakaan.py'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Ada Kesalahan umum');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop50Arrow'), 'Marker id unik oop50');
check(str_contains($body, 'buat_item') && str_contains($body, 'DendaFlat'), 'Cover Factory + Strategy');
check(str_contains($body, 'over-engineer') || str_contains($body, 'Over-engineer'), 'Peringatan jangan over-engineer');
check(str_contains($body, 'Tier 2'), 'Framing Tier 2');
check(str_contains($body, '10/10'), 'Sebut Seri 3 10/10');
check(substr_count($body, 'language-python') >= 5, 'Minimal 5 blok language-python');
check(str_contains($body, 'class Perpustakaan') && str_contains($body, 'ganti_strategi'), 'Full code Perpustakaan + ganti strategi');
$plainCheck = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#(?:4[0-9]|5[2-9])(?!\s*\(ini\))/', $plainCheck), 'Tidak ada bare #40–#49/#52+ di prosa');
$factoryPos = strpos($body, 'Factory — satu pintu');
$svgPos = strpos($body, 'oop50Arrow');
check($factoryPos !== false && $svgPos !== false && $svgPos < $factoryPos, 'SVG sebelum section Factory detail');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article50.php'), 'audit-article50.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-50'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-50'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle50'), 'DeployController');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php'), 'design-pattern'), 'Tag design-pattern di TagSeeder');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Latihan taut ke #44');
check(! str_contains($body, 'input('), 'Tidak ada input()');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php'), 'design-pattern-factory-strategy-python'), 'Backlink #49→#50');
$deploySrc = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
check(str_contains($deploySrc, 'Article49Seeder') && str_contains($deploySrc, 'publishArticle50'), 'Hook #50 bundling reseed #49');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
