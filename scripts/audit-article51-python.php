<?php

/**
 * Compile-check blok language-python Article51Seeder + pedagogi.
 * Usage: php scripts/audit-article51-python.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article51Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article51Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 5, 'Minimal 5 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a51_'.uniqid();
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
check(! preg_match('/#51(?!\s*\(ini\))/', $plain), 'Tidak ada plain #51 selain bentuk #51 (ini)');
check(str_contains($body, 'FakePin'), 'Ada FakePin');
check(str_contains($body, 'class Node'), 'Ada Node');
check(str_contains($body, 'def demo('), 'Ada demo()');
check(! str_contains($body, 'input('), 'Tidak ada input()');
check(str_contains($body, 'node_micropython_oop.py'), 'File contoh');
check(str_contains($body, 'Tier 2'), 'Framing Tier 2');
check(str_contains($body, '/artikel/design-pattern-factory-strategy-python'), 'Link #50');
check(str_contains($body, 'oop51Arrow'), 'SVG marker oop51');
check(! preg_match('/\/artikel\/[a-z0-9-]*(flask|fastapi)/', $body), 'Tidak hardlink slug #52 unpublished');
check(str_contains($body, 'label(suhu)'), 'tick() teruskan suhu ke label (hindari double baca)');
check(str_contains($body, 'Satu bacaan per tick') || str_contains($body, 'label(suhu)'), 'Pedagogi satu bacaan per tick');

// Runnable: blok kode lengkap (yang punya demo) harus cocok output artikel
$fullIdx = null;
foreach ($blocks as $i => $raw) {
    if (str_contains($raw, 'def demo(') && str_contains($raw, 'node_micropython_oop') === false) {
        // docstring may mention filename outside; look for demo in decoded
    }
    $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (str_contains($decoded, 'def demo(') && str_contains($decoded, 'if __name__')) {
        $fullIdx = $i;
        break;
    }
}
check($fullIdx !== null, 'Ada blok kode lengkap dengan demo()');

if ($fullIdx !== null) {
    $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a51_run_'.uniqid().'.py';
    $code = html_entity_decode($blocks[$fullIdx], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    file_put_contents($tmp, $code);
    $out = [];
    $rc = 0;
    exec('python '.escapeshellarg($tmp).' 2>&1', $out, $rc);
    @unlink($tmp);
    $joined = implode("\n", $out);
    check($rc === 0, 'demo() runnable exit 0'.($rc === 0 ? '' : ' — '.$joined));
    check(str_contains($joined, 'DHT22: 28.0 C | LED OFF'), 'demo output tick 1 (28.0 OFF)');
    check(str_contains($joined, 'DHT22: 31.5 C | LED ON'), 'demo output tick 2 (31.5 ON)');
    check(str_contains($joined, 'DHT22: 29.0 C | LED OFF'), 'demo output tick 3 (29.0 OFF)');
    check(str_contains($body, 'Kebun-A | DHT22: 28.0 C | LED OFF')
        && str_contains($body, 'Kebun-A | DHT22: 31.5 C | LED ON')
        && str_contains($body, 'Kebun-A | DHT22: 29.0 C | LED OFF'), 'Artikel menyimpan output yang cocok demo');
}

echo "\n=== Python/pedagogi audit #51: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
