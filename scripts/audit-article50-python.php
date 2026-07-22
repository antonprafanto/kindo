<?php

/**
 * Compile-check semua blok language-python di Article50Seeder + pedagogi.
 * Usage: php scripts/audit-article50-python.php
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

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 5, 'Minimal 5 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a50_'.uniqid();
mkdir($tmpDir);

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.py';
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('python -m py_compile '.escapeshellarg($file).' 2>&1', $out, $rc);
    check($rc === 0, 'py_compile block #'.($i + 1).(empty($out) ? '' : ' — '.implode(' ', $out)));
}

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#50(?!\s*\(ini\))/', $plain), 'Tidak ada plain #50 selain bentuk #50 (ini)');
check(str_contains($body, 'buat_item'), 'Ada buat_item');
check(str_contains($body, 'DendaFlat') && str_contains($body, 'DendaPerHari'), 'Ada DendaFlat/DendaPerHari');
check(str_contains($body, 'StrategiDenda'), 'Ada StrategiDenda');
check(str_contains($body, 'factory_strategy_perpustakaan.py'), 'File contoh');
check(str_contains($body, 'def demo('), 'Ada demo()');
check(! str_contains($body, 'input('), 'Tidak ada input() menggantung');
check(str_contains($body, 'class Perpustakaan'), 'Perpustakaan di kode lengkap');
check(str_contains($body, 'ganti_strategi'), 'Demo ganti strategi runtime');
check(str_contains($body, 'def items') || str_contains($body, 'def items(') || str_contains($body, 'def items(self)'), 'Property items (bukan akses _items dari demo)');
check(! preg_match('/for item in lib\._items/', $body), 'Demo tidak loop lib._items');
check(str_contains($body, 'jenis tidak dikenal: majalah') || str_contains($body, 'error: jenis tidak dikenal'), 'Demo ValueError jenis tidak dikenal');
check(str_contains($body, 'Tier 2'), 'Framing Tier 2');
check(! str_contains($body, 'draft <strong>#50') && ! str_contains($body, 'draft <strong>#50 (ini)'), 'Footer bukan “draft #50”');
check(str_contains($body, '/artikel/encapsulation-property-python-oop'), 'Link #43 encapsulation');
check(str_contains($body, '/artikel/capstone-sistem-perpustakaan-mini-oop-python'), 'Link #49');
check(str_contains($body, '/artikel/polymorphism-python-oop'), 'Link #45');
check(str_contains($body, 'oop50Arrow'), 'SVG marker oop50');
check(! preg_match('/\/artikel\/[a-z0-9-]*(micropython|flask-fastapi|oop-micropython)/', $body), 'Tidak hardlink slug #51/#52 unpublished');
check(str_contains($body, 'Factory di level Tier 2') || str_contains($body, 'level Tier 2'), 'Wording Factory Tier 2 (bukan “level Seri 3”)');

echo "\n=== Python/pedagogi audit #50: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
