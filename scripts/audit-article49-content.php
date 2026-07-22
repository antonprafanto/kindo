<?php

/**
 * Content / checklist audit #49.
 * Usage: php scripts/audit-article49-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article49Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article49Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');

echo "=== Content / checklist audit #49 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5\d|[6-9]\d)(?!\s*\(ini\))/', $plain), 'Tidak ada plain #50+ di luar link/pre');
check(str_contains($body, '/artikel/design-pattern-factory-strategy-python'), 'Hardlink Tier 2 #50');
check(str_contains($body, '/artikel/oop-micropython-esp32-class-sensor'), 'Hardlink Tier 2 #51 MicroPython');
check(substr_count($body, '/artikel/oop-micropython-esp32-class-sensor') >= 2, 'Capstone ≥2 hardlink #51');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Hardlink Tier 2 #52 Flask/FastAPI');
check(! str_contains($body, 'Ide berikutnya (belum live): MicroPython'), 'Tanpa residual MicroPython belum live');
check(! str_contains($body, 'belum jadi artikel live'), 'Tidak residual “belum jadi artikel live” untuk Tier 2');
check(str_contains($body, '#49 (ini)'), 'Self-ref #49 (ini)');
check(! preg_match('/#49(?!\s*\(ini\))/', $plain), 'Tidak ada plain #49 selain #49 (ini)');

$seriesLinks = [
    'mengenal-oop-cara-berpikir-dengan-objek-python' => '#40',
    'class-dan-object-pertama-python' => '#41',
    'attribute-method-constructor-init-python' => '#42',
    'encapsulation-property-python-oop' => '#43',
    'inheritance-pewarisan-class-python' => '#44',
    'polymorphism-python-oop' => '#45',
    'abstraction-abc-python-oop' => '#46',
    'composition-vs-inheritance-python' => '#47',
    'special-methods-dataclass-python' => '#48',
];
foreach ($seriesLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/'.$linkSlug), "Tautan ke {$label}");
}

check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tidak ada ASCII box-drawing');
check(substr_count($body, 'background:#F5F5F0') >= 2, 'Minimal 2 figure bg #F5F5F0');
check(str_contains($body, 'Pola Dasar'), 'Ada Pola Dasar');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'Kode lengkap'), 'Ada Kode lengkap');
check(str_contains($body, 'perpustakaan_mini.py'), 'File contoh');
check(str_contains($body, 'Latihan'), 'Ada Latihan');
check(str_contains($body, 'FAQ'), 'Ada FAQ');
check(str_contains($body, 'Kesalahan'), 'Ada Kesalahan');
check(str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop49Arrow'), 'Marker id unik oop49');
check(str_contains($body, 'dataclass'), 'Cover dataclass');
check(str_contains($body, '__str__') && str_contains($body, '__repr__') && str_contains($body, '__eq__'), 'Jembatan #48 dunder (__str__/__repr__/__eq__)');
check(str_contains($body, 'encapsulation-property-python-oop'), 'Taut encapsulation #43');
check(str_contains($body, 'pinjam_judul') || str_contains($body, 'koleksi[0].pinjam'), 'Jembatan latihan pinjam lewat layanan');
check(str_contains($body, 'stok hanya berubah lewat'), 'Batas encapsulation dijelaskan');
check(str_contains($body, 'ABC') && str_contains($body, 'abstractmethod'), 'Cover ABC/abstractmethod');
check(str_contains($body, 'class Perpustakaan'), 'Ada class Perpustakaan');
check(str_contains($body, 'koleksi'), 'Ada koleksi');
check(str_contains($body, 'demo('), 'Ada demo(');
check(str_contains($body, 'kembalikan'), 'Fitur kembalikan');
check(! str_contains($body, 'dataclass, field'), 'Tidak import field tak terpakai');
check(str_contains($src, 'dataclass'), 'Tag dataclass di seeder src');
preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $pyMatches);
$pyJoined = implode("\n", $pyMatches[1] ?? []);
check(! str_contains($pyJoined, 'input('), 'Tidak ada input() di blok Python');
check(str_contains($pyJoined, 'def __str__'), '__str__ di kode lengkap');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Latihan taut #44');
check(str_contains($body, 'Indeks Seri 3'), 'Ada Indeks Seri 3');
check(str_contains($body, '10/10'), 'Progress 10/10');
check(str_contains($src, "'is_featured'") && preg_match("/'is_featured'\s*=>\s*true/", $src), 'is_featured true di src');
check(! preg_match("/'cover_image'\s*=>/", $src), 'Cover tidak di-overwrite');
check(file_exists(__DIR__.'/audit-article49.php'), 'audit-article49.php ada');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-49'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-49'), 'CI step');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle49'), 'DeployController');
$deploySrc = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
check(str_contains($deploySrc, 'Article48Seeder') && str_contains($deploySrc, 'Article40Seeder') && str_contains($deploySrc, 'publishArticle49'), 'Hook #49 bundling reseed #48+#40');
check(substr_count($body, 'language-python') >= 7, 'Minimal 7 blok language-python');

$a48 = file_get_contents(__DIR__.'/../database/seeders/Article48Seeder.php');
check(str_contains($a48, 'capstone-sistem-perpustakaan-mini-oop-python') && ! str_contains($a48, 'Belum di-hyperlink'), 'Backlink #48 hardlink capstone tanpa residual “Belum di-hyperlink”');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php'), 'capstone-sistem-perpustakaan-mini-oop-python'), 'Indeks #40→#49');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
