<?php

/**
 * Compile-check semua blok language-python di Article49Seeder + pedagogi.
 * Usage: php scripts/audit-article49-python.php
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

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 7, 'Minimal 7 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a49_'.uniqid();
mkdir($tmpDir);

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    check(! str_contains($code, 'input('), 'Tidak ada input( di block #'.($i + 1));
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
check(! preg_match('/#49(?!\s*\(ini\))/', $plain), 'Tidak ada plain #49 selain bentuk #49 (ini)');
check(str_contains($body, '#49 (ini)'), 'Self-ref #49 (ini)');
check(str_contains($body, '10/10'), 'Progress 10/10');
check(str_contains($body, 'oop49Arrow'), 'SVG marker oop49');
check(str_contains($body, 'perpustakaan_mini.py'), 'File contoh');
check(str_contains($body, 'Perpustakaan'), 'Ada Perpustakaan');
check(str_contains($body, 'dataclass'), 'Ada dataclass');
check(str_contains($body, '__str__'), 'Ada __str__');
check(str_contains($body, '__repr__') && str_contains($body, '__eq__'), 'Sebut __repr__/__eq__');
check(! str_contains($body, 'dataclass, field'), 'Tidak import field tak terpakai');
check(str_contains($body, 'Pinjaman'), 'Ada Pinjaman');
check(str_contains($body, 'kembalikan'), 'Ada kembalikan');
check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-python'), 'Link #40');
check(str_contains($body, '/artikel/class-dan-object-pertama-python'), 'Link #41');
check(str_contains($body, '/artikel/attribute-method-constructor-init-python'), 'Link #42');
check(str_contains($body, '/artikel/encapsulation-property-python-oop'), 'Link #43');
check(str_contains($body, '/artikel/inheritance-pewarisan-class-python'), 'Link #44');
check(str_contains($body, '/artikel/polymorphism-python-oop'), 'Link #45');
check(str_contains($body, '/artikel/abstraction-abc-python-oop'), 'Link #46');
check(str_contains($body, '/artikel/composition-vs-inheritance-python'), 'Link #47');
check(str_contains($body, '/artikel/special-methods-dataclass-python'), 'Link #48');

echo "\n=== Python/pedagogi audit #49: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
