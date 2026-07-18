<?php

/**
 * Compile-check semua blok language-python di Article47Seeder + pedagogi.
 * Usage: php scripts/audit-article47-python.php
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

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 7, 'Minimal 7 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a47_'.uniqid();
mkdir($tmpDir);

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.py';
    file_put_contents($file, $code);
    $out = [];
    $exit = 0;
    exec('python -m py_compile '.escapeshellarg($file).' 2>&1', $out, $exit);
    check($exit === 0, 'py_compile block #'.($i + 1).(empty($out) ? '' : ' — '.implode(' ', $out)));
}

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#47(?!\s*\(ini\))/', $plain), 'Tidak ada plain #47 selain bentuk #47 (ini)');
check(str_contains($body, 'PerpustakaanSalah'), 'Anti-pola PerpustakaanSalah');
check(str_contains($body, 'self.koleksi'), 'Composition self.koleksi');
check(str_contains($body, 'class Ebook(Buku)'), 'Inheritance is-a tetap ditampilkan');
check(str_contains($body, 'Perpustakaan adalah Buku? False') || str_contains($body, 'isinstance(lib, Buku)'), 'Kontras isinstance composition');
check(str_contains($body, 'SALAH') && str_contains($body, 'BENAR'), 'Refactor SALAH/BENAR');
check(str_contains($body, 'def cari'), 'Method cari di pemilik koleksi');
check(str_contains($body, 'KatalogSalah') && str_contains($body, 'KatalogBenar'), 'Demo warisi list vs composition');
check(str_contains($body, 'perpustakaan_komposisi.py'), 'File contoh');
check(str_contains($body, 'Output yang diharapkan'), 'Ada output contoh lengkap');
check(str_contains($body, 'dataclass') || str_contains($body, 'Special Methods'), 'Teaser/link #48');
check(str_contains($body, '9/10 artikel live'), 'Progress 9/10 live');
check(str_contains($body, '/artikel/special-methods-dataclass-python'), 'Backlink live ke #48');
check(str_contains($body, 'sering berpasangan'), 'Jembatan composition + ABC');
check(str_contains($body, '/artikel/attribute-method-constructor-init-python') && str_contains($body, '/artikel/class-dan-object-pertama-python'), 'Footer/prasyarat #42+#41');
check(! str_contains($body, '"→"') && ! str_contains($body, "'→'"), 'Print pakai ASCII -> bila ada string panah');
$plainNoLink = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/ABC\s*\(#46\)/', $plainNoLink), 'Tidak ada bare ABC (#46)');
check(! preg_match('/#46(?!\s*\(ini\))/', $plainNoLink), 'Tidak ada bare #46 di prosa');

$compPos = strpos($body, 'Composition — Perpustakaan');
$svgPos = strpos($body, 'oop47Arrow');
check($compPos !== false && $svgPos !== false && $svgPos > $compPos, 'SVG setelah section composition');

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

echo "\n=== Python/pedagogi audit #47: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
