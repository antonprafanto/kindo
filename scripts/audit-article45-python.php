<?php

/**
 * Compile-check semua blok language-python di Article45Seeder + pedagogi.
 * Usage: php scripts/audit-article45-python.php
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

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 7, 'Minimal 7 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a45_'.uniqid();
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
check(! preg_match('/#45(?!\s*\(ini\))/', $plain), 'Tidak ada plain #45 selain bentuk #45 (ini)');
check(str_contains($body, 'for item in koleksi'), 'Ada loop koleksi');
check(str_contains($body, 'KatalogEntry'), 'Ada duck typing KatalogEntry');
check(str_contains($body, 'cetak_salah') && str_contains($body, 'cetak_benar'), 'Demo SALAH/BENAR isinstance');
check(str_contains($body, 'unduh'), 'isinstance untuk unduh');
check(str_contains($body, 'tipe object yang sebenarnya'), 'Jelaskan method lookup tipe aktual');
check(str_contains($body, 'AttributeError') && str_contains($body, 'dict'), 'Demo AttributeError duck typing');
check(str_contains($body, 'urutan Ebook') || str_contains($body, 'Ebook→Buku sudah'), 'Bedakan urutan aman vs pola hutan isinstance');
check(str_contains($body, 'cabang Buku') && str_contains($body, 'tidak pernah'), 'Demo runnable jebakan urutan isinstance');
check(str_contains($body, 'anti-pola') || str_contains($body, 'cetak_salah(koleksi)'), 'Tandai cetak_salah sebagai anti-pola');
check(str_contains($body, 'ESP32 Praktis · Budi (2023) · stok 1'), 'Output contoh setelah loop inti');
check(str_contains($body, 'cek tipe anak'), 'Jebakan urutan isinstance');
check(str_contains($body, 'isinstance(ebook, Buku)'), 'FAQ isinstance ebook→Buku');
check(str_contains($body, 'Audiobook'), 'Ada Audiobook');
check(str_contains($body, 'Output yang diharapkan'), 'Ada output contoh');
check(str_contains($body, 'Abstraction') || str_contains($body, 'ABC'), 'Teaser Abstraction');
check(str_contains($body, '8/10 artikel live'), 'Progress 8/10 live');

$loopPos = strpos($body, 'Satu loop untuk semua');
$svgPos = strpos($body, 'oop45Arrow');
check($loopPos !== false && $svgPos !== false && $svgPos > $loopPos, 'SVG setelah section loop');

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

echo "\n=== Python/pedagogi audit #45: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
