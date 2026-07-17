<?php

/**
 * Compile-check semua blok language-python di Article43Seeder + pedagogi.
 * Usage: php scripts/audit-article43-python.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article43Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article43Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 4, 'Minimal 4 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a43_'.uniqid();
mkdir($tmpDir);

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Strip comment-only lines that are intentional ValueError demos — keep as comments
    $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.py';
    file_put_contents($file, $code);
    $out = [];
    $exit = 0;
    exec('python -m py_compile '.escapeshellarg($file).' 2>&1', $out, $exit);
    check($exit === 0, 'py_compile block #'.($i + 1).(empty($out) ? '' : ' — '.implode(' ', $out)));
}

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#43(?!\s*\(ini\))/', $plain), 'Tidak ada plain #43 selain bentuk #43 (ini)');
check(str_contains($body, '@tahun.setter') || str_contains($body, '@stok.setter'), 'Ada setter di kode');
check(str_contains($body, 'self._stok'), 'Pakai _stok internal');
check(str_contains($body, 'RecursionError'), 'Peringatan RecursionError');
check(str_contains($body, 'ValueError("tahun tidak masuk akal")') || str_contains($body, 'tahun tidak masuk akal'), 'Validasi tahun');
check(str_contains($body, 'stok tidak boleh negatif'), 'Validasi stok negatif');
check(! str_contains($body, 'dipinjam'), 'Tidak ada dipinjam yatim');
check(str_contains($body, 'def pinjam(self)'), 'pinjam utuh');
check(str_contains($body, 'Inheritance'), 'Teaser Inheritance');

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

echo "\n=== Python/pedagogi audit #43: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
