<?php

/**
 * Compile-check semua blok language-python di Article46Seeder + pedagogi.
 * Usage: php scripts/audit-article46-python.php
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

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 8, 'Minimal 8 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a46_'.uniqid();
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
check(! preg_match('/#46(?!\s*\(ini\))/', $plain), 'Tidak ada plain #46 selain bentuk #46 (ini)');
check(str_contains($body, 'from abc import'), 'Import abc');
check(str_contains($body, 'class Pinjaman'), 'Kontrak Pinjaman');
check(str_contains($body, 'abstractmethod'), 'abstractmethod');
check(str_contains($body, 'BukuFisik') && str_contains($body, 'EbookLisensi'), 'Dua implementasi');
check(str_contains($body, 'BukuBelumSiap'), 'Demo BukuBelumSiap');
check(str_contains($body, 'TypeError'), 'Sebut TypeError');
check(str_contains($body, 'tidak diinstansiasi') || str_contains($body, 'Can\'t instantiate'), 'Jelaskan ABC tidak diinstansiasi');
check(str_contains($body, 'isinstance(item, Pinjaman)'), 'isinstance kontrak di loop');
check(str_contains($body, 'hutan'), 'Bedakan hutan isinstance');
check(str_contains($body, 'def label'), 'Method konkret label');
check(str_contains($body, 'pinjam fisik ESP32 Praktis'), 'Output contoh loop');
check(str_contains($body, 'Output yang diharapkan'), 'Ada output contoh');
check(str_contains($body, 'Composition'), 'Teaser Composition');
check(str_contains($body, '9/10 artikel live'), 'Progress 9/10 live');
check(str_contains($body, 'kontrak_pinjaman.py'), 'File contoh');
check(str_contains($body, 'tanpa mewarisi') || str_contains($body, 'harus mewarisi'), 'FAQ wajib warisi ABC');
check(str_contains($body, 'except TypeError'), 'Demo runnable TypeError BukuBelumSiap');
check(str_contains($body, 'pinjam fisik ESP32 Praktis · sisa 1'), 'Output setelah isi kontrak');
check(str_contains($body, 'isinstance(..., Pinjaman)'), 'Pola Dasar pakai isinstance Pinjaman');
check(str_contains($body, 'EntriDuck') && str_contains($body, 'isinstance duck'), 'Demo duck typing vs isinstance ABC');
check(str_contains($body, 'BukuFisik -> pinjam Cerita Sensor'), 'Output demo label()');
check(! str_contains($body, '"→"') && ! str_contains($body, "'→'"), 'Print pakai ASCII -> (aman konsol Windows)');
$plainNoLink = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! str_contains($plainNoLink, 'anti-pola #45'), 'Tidak ada bare anti-pola #45');
check(! preg_match('/Encapsulation\s*\(#43\)/', $plainNoLink), 'Tidak ada bare Encapsulation (#43)');

$implPos = strpos($body, 'Isi kontrak');
$svgPos = strpos($body, 'oop46Arrow');
check($implPos !== false && $svgPos !== false && $svgPos > $implPos, 'SVG setelah section implementasi');

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

echo "\n=== Python/pedagogi audit #46: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
