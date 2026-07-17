<?php

/**
 * Compile-check semua blok language-python di Article42Seeder.
 * Usage: php scripts/audit-article42-python.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article42Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article42Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $blocks);
$codes = $blocks[1] ?? [];

check(count($codes) >= 5, 'Minimal 5 blok language-python');

$python = trim(shell_exec('where python 2>NUL') ?: '') !== '' ? 'python' : 'py';

foreach ($codes as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a42_'.$i.'.py';
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec($python.' -m py_compile '.escapeshellarg($file).' 2>&1', $out, $rc);
    @unlink($file);
    check($rc === 0, 'py_compile block #'.($i + 1).( $rc === 0 ? '' : ' — '.implode(' ', $out)));
}

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#42(?!\s*\(ini\))/', $plain), 'Tidak ada plain #42 selain bentuk #42 (ini)');
check(str_contains($body, 'ValueError("tahun tidak masuk akal")'), 'Contoh validasi tahun di __init__');
check(str_contains($body, '# Buku("Kuno"') || str_contains($body, 'tahun tidak masuk akal'), 'Contoh komentar ValueError tahun');
check(str_contains($body, 'Di <a href="/artikel/class-dan-object-pertama-python">#41</a>') || str_contains($body, 'Di <a href="/artikel/class-dan-object-pertama-python">'), 'Bridge self merujuk #41');
check(! str_contains($body, 'dipinjam'), 'Tidak ada dipinjam');
check(str_contains($body, 'class Buku:') && str_contains($body, 'def pinjam_untuk(self'), 'pinjam_untuk utuh');
check(str_contains($body, 'Contoh class lengkap dengan method'), 'Prose pinjam_untuk tidak menyesatkan');

echo "\n=== Python/pedagogi audit #42: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
